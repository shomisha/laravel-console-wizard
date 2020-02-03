<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\Question;
use Shomisha\LaravelConsoleWizard\Questions\ChoiceQuestion;
use Shomisha\LaravelConsoleWizard\Questions\TextQuestion;

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

    public function askingName()
    {
    }

    public function askingAge()
    {

    }

    public function answeredAge(Question $question, $answer)
    {
        return $answer;
    }

    public function answeredPreferredLanguage(Question $question, $answer)
    {
        return $answer;
    }

    function completed()
    {
        return $this->answers;
    }
}