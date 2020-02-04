<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

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
        $answers = [];
        $options = array_merge($this->choices, [$this->endKeyword]);

        do {
            $newAnswer = $wizard->choice($this->text, $options);

            $answers[] = $newAnswer;

            $this->removeChoiceFromOptions($newAnswer, $options);
        } while ($newAnswer !== $this->endKeyword && count($options) > 0);

        if (!$this->retainEndKeywordInAnswers) {
            array_pop($answers);
        }

        return $answers;
    }

    final protected function removeChoiceFromOptions($choice, &$options)
    {
        unset($options[array_search($choice, $options)]);
    }
}