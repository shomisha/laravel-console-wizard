<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Exception\InvalidStepException;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class InvalidStepsTestWizard extends Wizard
{
    protected $signature = 'console-wizard-test:invalid-steps';

    function getSteps(): array
    {
        return [
            'valid' => new TextStep("Do you think I am valid enough?"),
            'invalid' => new InvalidStepException("I am definitely not valid."),
        ];
    }

    function completed()
    {
        // TODO: Implement completed() method.
    }
}