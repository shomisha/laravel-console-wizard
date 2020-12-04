<?php

namespace Shomisha\LaravelConsoleWizard\Command\Generators;

use Shomisha\LaravelConsoleWizard\Command\Generators\Subwizards\StepSubwizard;
use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class GenerateWizardWizard extends Wizard
{
    protected $signature = 'wizard:generate';

    function getSteps(): array
    {
        return [
            'name'              => new TextStep("Enter the class name for your wizard"),
            'signature'         => new TextStep("Enter the signature for your wizard"),
            'description'       => new TextStep("Enter the description for your wizard"),
            'steps'             => $this->repeat(
                $this->subWizard(new StepSubwizard())
            )->withRepeatPrompt("Do you want to add a wizard step?", true),
        ];
    }

    function completed()
    {
        dd($this->answers);
    }
}
