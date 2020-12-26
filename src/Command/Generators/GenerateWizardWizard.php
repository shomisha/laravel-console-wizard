<?php

namespace Shomisha\LaravelConsoleWizard\Command\Generators;

use Shomisha\LaravelConsoleWizard\Command\Generators\Subwizards\StepSubwizard;
use Shomisha\LaravelConsoleWizard\Command\GeneratorWizard;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\DataTransfer\WizardSpecification;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;
use Shomisha\LaravelConsoleWizard\Templates\WizardTemplate;

class GenerateWizardWizard extends GeneratorWizard
{
    protected $signature = 'wizard:generate';

    protected $type = 'Wizard';

    function getSteps(): array
    {
        return [
            'signature'         => new TextStep("Enter the signature for your wizard"),
            'description'       => new TextStep("Enter the description for your wizard"),
            'steps'             => $this->repeat(
                $this->subWizard(new StepSubwizard())
            )->withRepeatPrompt("Do you want to add a wizard step?", true),
        ];
    }

    protected function getNameStep(): Step
    {
        return new TextStep("Enter the class name for your wizard");
    }

    protected function generateTarget(): string
    {
        $specification = WizardSpecification::fromArray($this->answers->all())
                                            ->setName($this->getClassShortName())
                                            ->setNamespace($this->getClassNamespace());

        return WizardTemplate::bySpecification($specification)->print();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . "\Console\Command";
    }
}
