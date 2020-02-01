<?php

namespace Shomisha\LaravelConsoleWizard\Questions;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class MultipleAnswerTextQuestion extends BaseMultipleAnswerQuestion
{
    final public function ask(Wizard $wizard)
    {
        $wizard->line($this->text);
        $answers = [];

        do {
            $newAnswer = readline();

            $answers[] = $newAnswer;
        } while (strtolower($newAnswer) !== strtolower($this->endKeyword));

        if (!$this->retainEndKeywordInAnswers) {
            array_pop($answers);
        }

        return $answers;
    }
}