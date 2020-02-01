<?php

namespace Shomisha\LaravelConsoleWizard\Questions;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class ChoiceQuestion extends BaseQuestion
{
    /** @var array */
    private $options;

    public function __construct(string $text, array $options)
    {
        parent::__construct($text);

        $this->options = $options;
    }

    final public function ask(Wizard $wizard)
    {
        return $wizard->choice($this->text, $this->options);
    }
}