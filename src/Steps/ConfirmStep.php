<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class ConfirmStep extends BaseStep
{
    public function take(Wizard $wizard)
    {
        return $wizard->confirm($this->text);
    }
}