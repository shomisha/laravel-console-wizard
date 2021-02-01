<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Contracts\Wizard;

class ConfirmStep extends BaseStep
{
    private bool $defaultAnswer;

    public function __construct(string $text, bool $defaultAnswer = false)
    {
        parent::__construct($text);

        $this->defaultAnswer = $defaultAnswer;
    }

    public function take(Wizard $wizard)
    {
        return $wizard->confirm($this->text, $this->defaultAnswer);
    }
}
