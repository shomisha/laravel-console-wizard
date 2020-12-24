<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Contracts\Wizard;

class ChoiceStep extends BaseStep
{
    /** @var array */
    private $options;

    public function __construct(string $text, array $options)
    {
        parent::__construct($text);

        $this->options = $options;
    }

    final public function take(Wizard $wizard)
    {
        return $wizard->choice($this->text, $this->options);
    }
}
