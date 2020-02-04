<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

abstract class BaseMultipleAnswerStep extends BaseStep
{
    /** @var string */
    protected $endKeyword;

    /** @var bool */
    protected $retainEndKeywordInAnswers;

    public function __construct(string $text, array $options = [])
    {
        parent::__construct($text);

        $this->endKeyword = $options['end_keyword'] ?? 'Done';
        $this->retainEndKeywordInAnswers = $options['retain_end_keyword'] ?? false;
    }
}