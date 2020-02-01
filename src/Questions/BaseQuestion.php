<?php

namespace Shomisha\LaravelConsoleWizard\Questions;

use Shomisha\LaravelConsoleWizard\Contracts\Question;

abstract class BaseQuestion implements Question
{
    protected $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }
}