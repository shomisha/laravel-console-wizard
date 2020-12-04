<?php

namespace Shomisha\LaravelConsoleWizard\Command\Generators\Subwizards;

use Shomisha\LaravelConsoleWizard\Command\Subwizard;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class MultipleChoiceOptionsSubwizard extends Subwizard
{
    function getSteps(): array
    {
        return [
            'options' => $this->repeat(new TextStep("Add option for multiple choice (enter 'stop' to stop)"))->untilAnswerIs('stop')
        ];
    }
}
