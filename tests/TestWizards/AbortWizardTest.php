<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Steps\ConfirmStep;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class AbortWizardTest extends Wizard
{
    protected $signature = 'wizard:abort';

    function getSteps(): array
    {
        return [
            'abort' => new ConfirmStep('Should I abort?'),
            'why' => new TextStep("Why didn't you abort?"),
        ];
    }

    public function answeredAbort(Step $step, bool $abort)
    {
        if ($abort) {
            $this->abort("I am aborting");
        }

        return $abort;
    }

    function completed()
    {

    }
}
