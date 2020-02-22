<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class MultipleChoiceStep extends BaseMultipleAnswerStep
{
    /** @var array */
    private $choices;

    public function __construct(string $text, array $choices, array $options = [])
    {
        parent::__construct($text, $options);

        $this->choices = $choices;
    }

    final public function take(Wizard $wizard)
    {
        $options = array_merge($this->choices, [$this->endKeyword]);
        $answers = $this->loop(function () use ($wizard, $options) {
            return $wizard->choice($this->text, $options);
        });

        if ($this->shouldRemoveEndKeyword($answers)) {
            array_pop($answers);
        }

        return $answers;
    }
}
