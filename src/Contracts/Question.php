<?php

namespace Shomisha\LaravelConsoleWizard\Contracts;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

interface Question
{
    public function ask(Wizard $wizard);
}