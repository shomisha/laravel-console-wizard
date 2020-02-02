<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Questions\ChoiceQuestion;
use Shomisha\LaravelConsoleWizard\Questions\TextQuestion;
use Shomisha\LaravelConsoleWizard\Questions\UniqueMultipleChoiceQuestion;

class BaseTestWizard extends Wizard
{
    protected $signature = 'console-wizard-test:base';

    function getQuestions(): array
    {
        return [
            'name' => new TextQuestion("What's your name?"),
            'age' => new TextQuestion("How old are you?"),
            'preferred-language' => new ChoiceQuestion(
                'Your favourite programming language',
                [
                    'PHP',
                    'JavaScript',
                    'Python',
                    'Java',
                    'C#',
                    'Go',
                ]
            ),
        ];
    }

    function completed()
    {
        return $this->answers;
    }
}