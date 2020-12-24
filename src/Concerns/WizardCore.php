<?php

namespace Shomisha\LaravelConsoleWizard\Concerns;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Contracts\ValidatesWizard;
use Shomisha\LaravelConsoleWizard\Contracts\ValidatesWizardSteps;
use Shomisha\LaravelConsoleWizard\Contracts\Wizard;
use Shomisha\LaravelConsoleWizard\Exception\AbortWizardException;
use Shomisha\LaravelConsoleWizard\Exception\InvalidStepException;
use Shomisha\LaravelConsoleWizard\Steps\RepeatStep;

trait WizardCore
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $steps;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $taken;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $answers;

    /** @var \Illuminate\Support\Collection */
    protected $followup;

    /** @var \Illuminate\Support\Collection */
    protected $skipped;

    protected function initializeSteps()
    {
        $this->assertStepsAreValid($steps = $this->getSteps());

        $this->steps = collect($steps);

        $this->taken = collect([]);
        $this->followup = collect([]);
        $this->skipped = collect([]);
    }

    final public function take(Wizard $wizard)
    {
        while ($this->steps->isNotEmpty()) {
            $name = $this->steps->keys()->first();
            /** @var \Shomisha\LaravelConsoleWizard\Contracts\Step $step */
            $step = $this->steps->shift();

            try {
                $this->taking($step, $name);

                $answer = $step->take($this);

                if ($this->shouldValidateStep($name)) {
                    try {
                        $this->validateStep($name, $answer);
                    } catch (ValidationException $e) {
                        if (!$this->hasFailedValidationHandler($name)) {
                            throw $e;
                        }

                        $this->runFailedValidationHandler($name, $e, $answer);
                    }
                }

                $this->answered($step, $name, $answer);
            } catch (AbortWizardException $e) {
                $this->abortWizard($e->getUserMessage());
            }
        }

        return $this->answers->toArray();
    }

    final public function initializeWizard()
    {
        $this->initializeSteps();
        $this->initializeAnswers();
    }

    final protected function handleWizard()
    {
        $this->initializeWizard();

        $this->take($this);

        if ($this->shouldValidateWizard()) {
            try {
                $this->validateWizard();
            } catch (ValidationException $e) {
                $this->onWizardInvalid($e->errors());
            }
        }
    }

    final protected function subWizard(Wizard $wizard)
    {
        $wizard->output = $this->output;
        $wizard->input = $this->input;

        $wizard->initializeWizard();

        return $wizard;
    }

    final protected function repeat(Step $step)
    {
        return new RepeatStep($step);
    }

    final protected function followUp(string $name, Step $step)
    {
        $this->followup->put($name, $step);

        return $this;
    }

    final protected function skip(string $name)
    {
        $step = $this->steps->pull($name);

        if ($step !== null) {
            $this->skipped->put($name, $step);
        }
    }

    final protected function abort(string $message = null)
    {
        throw new AbortWizardException($message);
    }

    final protected function assertStepsAreValid(array $steps)
    {
        foreach ($steps as $step) {
            if (! ($step instanceof Step)) {
                $message = sprintf(
                    "%s does not implement the %s interface",
                    get_class($step),
                    Step::class
                );
                throw new InvalidStepException($message);
            }
        }
    }

    private function abortWizard(string $message = null) {
        if ($message) {
            $this->error($message);
        }

        exit(1);
    }

    private function initializeAnswers()
    {
        $this->answers = collect([]);
    }

    private function taking(Step $step, string $name)
    {
        if ($this->hasTakingModifier($name)) {
            $this->{$this->guessTakingModifier($name)}($step);
        }
    }

    private function hasTakingModifier(string $name)
    {
        return method_exists($this, $this->guessTakingModifier($name));
    }

    private function guessTakingModifier(string $name)
    {
        return sprintf('taking%s', Str::studly($name));
    }

    private function answered(Step $step, string $name, $answer)
    {
        if ($this->hasAnsweredModifier($name)) {
            $answer = $this->{$this->guessAnsweredModifier($name)}($step, $answer);
        }

        $this->addAnswer($name, $answer);

        $this->moveStepToTaken($name, $step);

        $this->flushFollowups();
    }

    private function hasAnsweredModifier(string $name)
    {
        return method_exists($this, $this->guessAnsweredModifier($name));
    }

    private function guessAnsweredModifier(string $name)
    {
        return sprintf('answered%s', Str::studly($name));
    }

    private function addAnswer(string $name, $answer)
    {
        $this->answers->put($name, $answer);
    }

    private function moveStepToTaken(string $name, Step $step)
    {
        $this->taken->put($name, $step);
    }

    private function flushFollowups()
    {
        $this->steps = collect(array_merge(
            $this->followup->reverse()->toArray(), $this->steps->toArray()
        ));

        $this->followup = collect([]);
    }

    private function shouldValidateWizard()
    {
        return $this instanceof ValidatesWizard;
    }

    private function validateWizard()
    {
        return $this->validate($this->answers->toArray(), $this->getRules());
    }

    private function shouldValidateStep(string $name)
    {
        return $this instanceof ValidatesWizardSteps
            && array_key_exists($name, $this->getRules());
    }

    private function validateStep(string $name, $answer)
    {
        return $this->validate(
            [$name => $answer],
            [$name => $this->getRules()[$name]]
        );
    }

    private function validate(array $data, array $rules)
    {
        return Validator::make($data, $rules)->validate();
    }

    private function hasFailedValidationHandler(string $name)
    {
        return method_exists($this, $this->guessValidationFailedHandlerName($name));
    }

    private function runFailedValidationHandler(string $name, ValidationException $exception, $answer)
    {
        $this->{$this->guessValidationFailedHandlerName($name)}($answer, $exception->errors()[$name]);
    }

    private function guessValidationFailedHandlerName(string $name)
    {
        return sprintf("onInvalid%s", Str::studly($name));
    }

    public function refill()
    {
        $this->initializeWizard();
    }
}
