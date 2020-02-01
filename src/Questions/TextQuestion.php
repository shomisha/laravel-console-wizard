<?php

namespace Shomisha\LaravelConsoleWizard\Questions;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class TextQuestion extends BaseQuestion
{
    final public function ask(Wizard $wizard)
    {
        return $wizard->ask($this->text);
    }
}