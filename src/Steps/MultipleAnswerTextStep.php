<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class MultipleAnswerTextStep extends BaseMultipleAnswerStep
{
    final public function take(Wizard $wizard)
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