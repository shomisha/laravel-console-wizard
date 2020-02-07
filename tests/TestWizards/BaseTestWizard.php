<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Steps\ChoiceStep;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class BaseTestWizard extends Wizard
{
    protected $signature = 'console-wizard-test:base';

    function getSteps(): array
    {
        return [
            'name' => new TextStep("What's your name?"),
            'age' => new TextStep("How old are you?"),
            'preferred-language' => new ChoiceStep(
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

    public function takingRunAnother()
    {
        $this->followup('followup', new TextStep("I am a followup."));
    }

    public function takingMainStep()
    {
        $this->followup('pre-main-step', new TextStep('I am added before the main step'));
    }

    public function answeredMainStep()
    {
        $this->followup('post-main-step', new TextStep('I am added after the main step'));
    }

    public function takingName()
    {
    }

    public function takingAge()
    {

    }

    public function answeredAge(Step $question, $answer)
    {
        return $answer;
    }

    public function answeredPreferredLanguage(Step $question, $answer)
    {
        return $answer;
    }

    function completed()
    {
        return $this->answers;
    }
}