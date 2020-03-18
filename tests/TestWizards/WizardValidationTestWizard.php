<?php

namespace Shomisha\LaravelConsoleWizard\Test\TestWizards;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\ValidatesWizard;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class WizardValidationTestWizard extends Wizard implements ValidatesWizard
{
    protected $signature = 'console-wizard-test:wizard-validation';

    public function getRules(): array
    {
        return [
            'favourite_band' => ['string',  'in:Kings of Leon,Milo Greene'],
            'country' => ['string', 'in:Serbia,England,Croatia,France'],
        ];
    }

    public function onWizardInvalid(array $errors)
    {
        $this->abort("Your wizard is invalid");
    }

    function getSteps(): array
    {
        return [
            'name' => new TextStep('What is your name?'),
            'favourite_band' => new TextStep('What is your favourite band?'),
            'country' => new TextStep('Which country do you come from?'),
        ];
    }

    function completed()
    {

    }
}
