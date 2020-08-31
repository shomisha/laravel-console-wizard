<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Steps\OneTimeWizard;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class WizardWithOneTimeSubwizard extends Wizard
{
    protected $signature = 'console-wizard-test:one-time-subwizard';

    function getSteps(): array
    {
        return [
            'one-time-subwizard' => $this->subWizard(new OneTimeWizard([
                'first-question' => new TextStep('Answer the first step'),
                'second-question' => new TextStep('Answer the second step'),
            ])),
        ];
    }

    function completed()
    {}
}
