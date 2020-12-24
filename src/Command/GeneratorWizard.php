<?php

namespace Shomisha\LaravelConsoleWizard\Command;

use Illuminate\Console\GeneratorCommand;
use Shomisha\LaravelConsoleWizard\Concerns\WizardCore;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Contracts\Wizard as WizardContract;

abstract class GeneratorWizard extends GeneratorCommand implements Step, WizardContract
{
    use WizardCore { initializeSteps as parentInitializeSteps; }

    const NAME_STEP_NAME = 'name_';

    public function handle()
    {
        $this->handleWizard();

        return parent::handle();
    }

    protected function initializeSteps()
    {
        $this->parentInitializeSteps();

        $this->steps->prepend($this->getNameStep(), self::NAME_STEP_NAME);
    }

    abstract protected function getNameStep(): Step;

    abstract protected function generateTarget(): string;

    final protected function getNameInput()
    {
        return $this->answers->get(self::NAME_STEP_NAME);
    }

    final protected function getClassFullName(): string
    {
        return $this->qualifyClass($this->getNameInput());
    }

    final protected function getClassShortName(): string
    {
        $name = $this->getNameInput();
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return $class;
    }

    final protected function getClassNamespace(): string
    {
        return $this->getNamespace($this->getClassFullName());
    }

    final protected function buildClass($name)
    {
        return $this->generateTarget();
    }

    final protected function getStub()
    {
        return;
    }
}
