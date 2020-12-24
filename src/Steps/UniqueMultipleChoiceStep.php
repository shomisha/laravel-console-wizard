<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Contracts\Wizard;

class UniqueMultipleChoiceStep extends BaseMultipleAnswerStep
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
        $answers = $this->loop(function () use ($wizard, &$options) {
            $newAnswer = $wizard->choice($this->text, $options);

            $this->removeChoiceFromOptions($newAnswer, $options);

            return $newAnswer;
        });

        if ($this->shouldRemoveEndKeyword($answers)) {
            array_pop($answers);
        }

        return $answers;
    }

    final protected function removeChoiceFromOptions($choice, &$options)
    {
        unset($options[array_search($choice, $options)]);
    }
}
