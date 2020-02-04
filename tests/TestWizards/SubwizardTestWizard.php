<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class SubwizardTestWizard extends Wizard
{
    protected $signature = 'console-wizard-test:subwizard';

    function getSteps(): array
    {
        return [
            'name' => new TextStep("What's your name?"),
            'legal-status' => $this->subWizard(new Subwizard()),
        ];
    }

    function completed()
    {
    }
}