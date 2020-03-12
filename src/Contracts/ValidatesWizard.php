<?php

namespace Shomisha\LaravelConsoleWizard\Contracts;

interface ValidatesWizard
{
    public function getRules(): array;

    public function onWizardInvalid(array $errors);
}
