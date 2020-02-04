# Laravel Console Wizard

[![Latest Stable Version](https://img.shields.io/packagist/v/shomisha/laravel-console-wizard)](https://packagist.org/packages/shomisha/laravel-console-wizard)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE.md)

This package provides a basis for creating multi-step wizards with complex input inside the console.
It is best used for customising generator command output, but can be used for handling any sort of tasks.

```php
<?php

namespace App\Console\Commands;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Steps\ChoiceStep;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;

class IntroductionWizard extends Wizard
{
    protected $signature = "wizard:introduction";

    protected $description = 'Introduction wizard.';

    public function getSteps(): array
    {
        return [
            'name'   => new TextStep("What's your name?"),
            'age'    => new TextStep("How old are you?"),
            'gender' => new ChoiceStep("Your gender?", ["Male", "Female"]),
        ];
    }

    public function completed()
    {
        $this->line(sprintf(
            "This is %s and %s is %s years old.",
            $this->answers->get('name'),
            ($this->answers->get('gender') === 'Male') ? 'he' : 'she',
            $this->answers->get('age')
        ));
    }
}
```
The example above shows a simple example of how you can create a wizard with several input prompts and then perform actions using the answers provided by the user.
Running `php artisan wizard:introduction` in your console would execute the above wizard and produce the following output:

```
shomisha:laravel-console-wizard shomisha$ php artisan wizard:introduction

 What's your name?:
 > Misa

 How old are you?:
 > 25

 Your gender?:
  [0] Male
  [1] Female
 > 0

This is Misa and he is 25 years old.
```

Take a look at our [wiki pages](https://github.com/weareneopix/laravel-model-translation/wiki) for more instructions and other Wizard features.