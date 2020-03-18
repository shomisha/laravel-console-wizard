<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\ValidatesWizardSteps;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class StepValidationTestWizard extends Wizard implements ValidatesWizardSteps
{
    protected $signature = 'console-wizard-test:step-validation';

    public function getRules(): array
    {
        return [
            'age' => ['integer', 'min:18'],
            'favourite_colour' => ['string', 'in:red,green,blue'],
        ];
    }

    function getSteps(): array
    {
        return [
            'name' => new TextStep("What is your name?"),
            'age' => new TextStep("How old are you?"),
            'favourite_colour' => new TextStep("What is your favourite colour?"),
        ];
    }

    public function onInvalidAge($answer, $errors)
    {
        $this->abort("The age you entered is invalid.");
    }

    function completed()
    {
    }
}
