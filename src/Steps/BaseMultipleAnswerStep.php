<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

abstract class BaseMultipleAnswerStep extends BaseStep
{
    protected string $endKeyword;

    protected bool $retainEndKeywordInAnswers;

    protected int $repetitions = 0;

    protected ?int $maxRepetitions = null;

    public function __construct(string $text, array $options = [])
    {
        parent::__construct($text);

        $this->endKeyword = $options['end_keyword'] ?? 'Done';
        $this->retainEndKeywordInAnswers = $options['retain_end_keyword'] ?? false;
        $this->maxRepetitions = $options['max_repetitions'] ?? null;
    }

    protected function loop(callable $callback)
    {
        $answers = [];

        do {
            $newAnswer = $callback();

            $answers[] = $newAnswer;

            $this->incrementRepetitions();
        } while ($this->shouldKeepLooping($newAnswer));

        return $answers;
    }

    protected function shouldKeepLooping($answer)
    {
        return strtolower($answer) !== strtolower($this->endKeyword) && !$this->hasExceededMaxRepetitions();
    }

    protected function incrementRepetitions()
    {
        $this->repetitions++;

        return $this;
    }

    protected function hasExceededMaxRepetitions()
    {
        return $this->maxRepetitions !== null && $this->repetitions >= $this->maxRepetitions;
    }

    protected function shouldRemoveEndKeyword(array $answers)
    {
        return !$this->retainEndKeywordInAnswers && strtolower(last($answers)) === strtolower($this->endKeyword);
    }
}
