<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\RepeatsInvalidSteps;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class RepeatsStepsTestWizard extends Wizard implements RepeatsInvalidSteps
{
    protected $signature = "wizard-test:repeat-invalid";

    function getSteps(): array
    {
        return [
            'age' => new TextStep("Enter age"),
            "birth_year" => new TextStep("Enter birth year"),
        ];
    }

    public function getRules(): array
    {
        return [
            "age" => ["integer", "min:10", "max:20"],
            "birth_year" => ["integer", "min:1990", "max:2000"],
        ];
    }

    public function onInvalidBirthYear($step, $answer)
    {
        $this->error("Wrong answer, nimrod");
    }

    function completed()
    {
        $this->info("Done");
    }
}
