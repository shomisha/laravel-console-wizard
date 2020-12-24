<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Contracts\Wizard;

class ConfirmStep extends BaseStep
{
    public function take(Wizard $wizard)
    {
        return $wizard->confirm($this->text);
    }
}
