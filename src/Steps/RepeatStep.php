<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Exception\InvalidStepException;

class RepeatStep implements Step
{
    /** @var \Shomisha\LaravelConsoleWizard\Command\Wizard */
    private $wizard;

    /** @var int */
    private $counter = 0;

    /** @var callable */
    private $callback = null;

    /** @var \Shomisha\LaravelConsoleWizard\Contracts\Step */
    private $step;

    /** @var bool  */
    private $excludeLast = false;

    public function __construct(Step $step)
    {
        $this->step = $step;
    }

    public function take(Wizard $wizard)
    {
        if ($this->callback === null) {
            throw new InvalidStepException(
                "The RepeatStep has not been properly initialized. Please call either RepeatStep::times() or RepeatStep::until() to initialize it."
            );
        }

        $this->wizard = $wizard;

        $answers = [];
        $answer = null;

        while (call_user_func($this->callback, $answer)) {
            $answer = $this->step->take($this->wizard);

            $answers[] = $answer;

            $this->counter++;

            if ($this->shouldRefillStep()) {
                $this->refillStep();
            }
        }

        if ($this->excludeLast) {
            array_pop($answers);
        }

        return $answers;
    }

    public function times(int $times)
    {
        return $this->until(function () use ($times) {
            return $this->counter == $times;
        });
    }

    public function untilAnswerIs($answer, int $maxRepetitions = null)
    {
        return $this->until(function ($actualAnswer) use ($answer) {
            if ($this->isFirstRun()) {
                return false;
            }

            return $actualAnswer === $answer;
        }, $maxRepetitions);
    }

    public function withRepeatPrompt(string $question, bool $askOnFirstRun = false)
    {
        return $this->until(function ($answer) use ($question, $askOnFirstRun) {
            if ($this->isFirstRun() && !$askOnFirstRun) {
                return false;
            }

            return !(new ConfirmStep($question))->take($this->wizard);
        });
    }

    public function until(callable $callback, int $maxRepetitions = null)
    {
        $this->callback = function ($answer) use ($callback, $maxRepetitions) {
            if ($callback($answer)) {
                return false;
            }

            if ($this->hasExceededMaxRepetitions($maxRepetitions)) {
                return false;
            }

            return true;
        };

        return $this;
    }

    public function withLastAnswer()
    {
        return $this->setExcludeLast(false);
    }

    public function withoutLastAnswer()
    {
        return $this->setExcludeLast(true);
    }

    public function setExcludeLast(bool $excludeLast)
    {
        $this->excludeLast = $excludeLast;

        return $this;
    }

    private function hasExceededMaxRepetitions($maxRepetitions)
    {
        return $maxRepetitions !== null && $this->counter >= $maxRepetitions;
    }

    private function shouldRefillStep()
    {
        return $this->step instanceof Wizard;
    }

    private function refillStep()
    {
        return $this->step->refill();
    }

    private function isFirstRun()
    {
        return $this->counter === 0;
    }
}
