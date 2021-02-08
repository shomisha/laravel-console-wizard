<?php

namespace Shomisha\LaravelConsoleWizard\Command;

use Illuminate\Console\Command;
use Shomisha\LaravelConsoleWizard\Concerns\WizardCore;
use Shomisha\LaravelConsoleWizard\Contracts\Wizard as WizardContract;
use Shomisha\LaravelConsoleWizard\Exception\AbortWizardException;

abstract class Wizard extends Command implements WizardContract
{
    use WizardCore;

    public function __construct()
    {
        parent::__construct();
    }

    final public function handle()
    {
        try {
            $this->handleWizard();
        } catch (AbortWizardException $e) {
            return $this->abortWizard($e);
        }

        $this->completed();
    }

    abstract function getSteps(): array;

    abstract function completed();
}
