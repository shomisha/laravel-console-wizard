<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class InheritAnswersTestWizard extends Wizard
{
    protected $signature = "wizard-test:inherit-answers {name?} {--age=}";

    protected bool $inheritAnswersFromArguments = true;

    function getSteps(): array
    {
        return [
            'name' => new TextStep('Name'),
            'age' => new TextStep('Age'),
        ];
    }

    function completed()
    {
        $this->info(sprintf(
            "%s is %s year(s) old",
            $this->answers->get('name'),
            $this->answers->get('age')
        ));
    }
}
