<?php

namespace Shomisha\LaravelConsoleWizard\Test;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Shomisha\LaravelConsoleWizard\LaravelConsoleWizardServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelConsoleWizardTestsServiceProvider::class,
        ];
    }
}