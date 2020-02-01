<?php

namespace Shomisha\LaravelConsoleWizard\Questions;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class ConfirmQuestion extends BaseQuestion
{
    public function ask(Wizard $wizard)
    {
        return $wizard->confirm($this->text);
    }
}