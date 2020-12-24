<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Contracts\Wizard;

class TextStep extends BaseStep
{
    final public function take(Wizard $wizard)
    {
        return $wizard->ask($this->text);
    }
}
