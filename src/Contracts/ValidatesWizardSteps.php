<?php

namespace Shomisha\LaravelConsoleWizard\Contracts;

interface ValidatesWizardSteps
{
    public function stepsToValidate(): array;

    public function getRules(): array;
}
