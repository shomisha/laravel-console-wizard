<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class MultipleChoiceStep extends BaseMultipleAnswerStep
{
    /** @var array */
    private $choices;

    public function __construct(string $text, array $choices, array $options)
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
        } while ($newAnswer !== $this->endKeyword);

        if (!$this->retainEndKeywordInAnswers) {
            array_pop($answers);
        }

        return $answers;
    }
}