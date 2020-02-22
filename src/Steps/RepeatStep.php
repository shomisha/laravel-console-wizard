<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Exception\InvalidStepException;

class RepeatStep implements Step
{
    /** @var int */
    private $counter = 0;

    /** @var callable */
    private $callback = null;

    /** @var \Shomisha\LaravelConsoleWizard\Contracts\Step */
    private $step;

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

        $answers = [];
        $answer = null;

        while (call_user_func($this->callback, $answer)) {
            $answer = $this->step->take($wizard);

            $answers[] = $answer;

            $this->counter++;

            if ($this->shouldRefillStep()) {
                $this->refillStep();
            }
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
            return $actualAnswer === $answer;
        }, $maxRepetitions);
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
}
