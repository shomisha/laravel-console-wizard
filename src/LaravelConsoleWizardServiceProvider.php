<?php

namespace Shomisha\LaravelConsoleWizard;

use Illuminate\Support\ServiceProvider;
use Shomisha\LaravelConsoleWizard\Command\Generators\GenerateWizardWizard;

class LaravelConsoleWizardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            GenerateWizardWizard::class,
        ]);
    }
}
