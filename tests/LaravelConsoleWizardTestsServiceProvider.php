<?php

namespace Shomisha\LaravelConsoleWizard\Test;

use Illuminate\Support\ServiceProvider;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\BaseTestWizard;

class LaravelConsoleWizardTestsServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->commands(
            BaseTestWizard::class,
        );
    }
}