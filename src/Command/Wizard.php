<?php

namespace Shomisha\LaravelConsoleWizard\Command;

use Illuminate\Console\Command;
use Shomisha\LaravelConsoleWizard\Concerns\WizardCore;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Contracts\Wizard as WizardContract;

abstract class Wizard extends Command implements Step, WizardContract
{
    use WizardCore;

    public function __construct()
    {
        parent::__construct();
    }

    final public function handle()
    {
        $this->handleWizard();

        $this->completed();
    }
    
    abstract function getSteps(): array;

    abstract function completed();
}
