<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Steps\ConfirmStep;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class Subwizard extends Wizard
{
    function getSteps(): array
    {
        return [
            'age-legality' => new ConfirmStep("Are you older than 18?"),
            'marital-legality' => new TextStep("Your marital status:"),
        ];
    }

    function completed()
    {
    }
}