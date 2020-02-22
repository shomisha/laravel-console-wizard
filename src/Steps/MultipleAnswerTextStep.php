<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class MultipleAnswerTextStep extends BaseMultipleAnswerStep
{
    final public function take(Wizard $wizard)
    {
        $wizard->line($this->text);

        $answers = $this->loop(function () use ($wizard) {
            return readline();
        });

        if ($this->shouldRemoveEndKeyword($answers)) {
            array_pop($answers);
        }

        return $answers;
    }
}
