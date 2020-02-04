<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Contracts\Step;

abstract class BaseStep implements Step
{
    protected $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }
}