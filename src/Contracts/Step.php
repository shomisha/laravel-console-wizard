<?php

namespace Shomisha\LaravelConsoleWizard\Contracts;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

interface Step
{
    public function take(Wizard $wizard);
}