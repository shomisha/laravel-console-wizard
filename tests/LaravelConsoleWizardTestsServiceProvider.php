<?php

namespace Shomisha\LaravelConsoleWizard\Test;

use Illuminate\Support\ServiceProvider;
use Shomisha\LaravelConsoleWizard\Command\Generators\GenerateWizardWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\AbortWizardTest;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\BaseTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\InheritAnswersTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\RepeatsStepsTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\StepValidationTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\SubwizardTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\WizardValidationTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\WizardWithOneTimeSubwizard;

class LaravelConsoleWizardTestsServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->commands(
            BaseTestWizard::class,
            SubwizardTestWizard::class,
            StepValidationTestWizard::class,
            WizardValidationTestWizard::class,
            WizardWithOneTimeSubwizard::class,
            GenerateWizardWizard::class,
            RepeatsStepsTestWizard::class,
            InheritAnswersTestWizard::class,
            AbortWizardTest::class,
        );
    }
}
