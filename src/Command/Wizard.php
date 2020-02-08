<?php

namespace Shomisha\LaravelConsoleWizard\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Exception\InvalidStepException;
use Shomisha\LaravelConsoleWizard\Steps\RepeatStep;

abstract class Wizard extends Command implements Step
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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    final public function handle()
    {
        $this->initializeWizard();

        $this->take($this);

        $this->completed();
    }

    final public function take(Wizard $wizard)
    {
        do {
            $name = $this->steps->keys()->first();
            /** @var \Shomisha\LaravelConsoleWizard\Contracts\Step $step */
            $step = $this->steps->shift();

            $this->taking($step, $name);

            $answer = $step->take($this);

            $this->answered($step, $name, $answer);
        } while ($this->steps->isNotEmpty());

        return $this->answers->toArray();
    }

    final public function initializeWizard()
    {
        $this->initializeSteps();
        $this->initializeAnswers();
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

    private function initializeSteps()
    {
        $this->assertStepsAreValid($steps = $this->getSteps());

        $this->steps = collect($steps);

        $this->taken = collect([]);
        $this->followup = collect([]);
        $this->skipped = collect([]);
    }

    private function initializeAnswers()
    {
        $this->answers = collect([]);
    }

    private function assertStepsAreValid(array $steps)
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

    private function getAnswer(string $name)
    {
        return $this->answers->get($name);
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

    abstract function getSteps(): array;

    abstract function completed();
}
