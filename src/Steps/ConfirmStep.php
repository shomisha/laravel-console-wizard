<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Contracts\Wizard;

class ConfirmStep extends BaseStep
{
    private $defaultAnswer;

    public function __construct(string $text, $defaultAnswer = false)
    {
        parent::__construct($text);

        $this->defaultAnswer = $defaultAnswer;
    }

    public function take(Wizard $wizard)
    {
        return $wizard->confirm($this->text, $this->getDefaultAnswer());
    }

    private function getDefaultAnswer(): bool
    {
        if (is_callable($this->defaultAnswer)) {
            return (bool) ($this->defaultAnswer)();
        }

        return (bool) $this->defaultAnswer;
    }
}
