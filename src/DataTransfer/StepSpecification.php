<?php

namespace Shomisha\LaravelConsoleWizard\DataTransfer;

use Shomisha\LaravelConsoleWizard\Exception\InvalidStepSpecificationException;
use Shomisha\LaravelConsoleWizard\Steps\ChoiceStep;
use Shomisha\LaravelConsoleWizard\Steps\MultipleChoiceStep;
use Shomisha\LaravelConsoleWizard\Steps\UniqueMultipleChoiceStep;

class StepSpecification extends Specification
{
    private const KEY_NAME = 'name';
    private const KEY_QUESTION = 'question';
    private const KEY_TYPE = 'type';
    private const KEY_OPTIONS = 'step-data.options';
    private const KEY_TAKING_MODIFIER = 'has_taking_modifier';
    private const KEY_ANSWERED_MODIFIER = 'has_answered_modifier';

    private const STEPS_WITH_OPTIONS = [
        ChoiceStep::class,
        MultipleChoiceStep::class,
        UniqueMultipleChoiceStep::class,
    ];

    public function getName(): string
    {
        return $this->extract(self::KEY_NAME);
    }

    public function getType(): string
    {
        return $this->extract(self::KEY_TYPE);
    }

    public function getQuestion(): string
    {
        return $this->extract(self::KEY_QUESTION);
    }

    public function hasOptions(): bool
    {
        if (empty($this->getOptions())) {
            return false;
        }

        return $this->stepShouldHaveOptions();
    }

    public function stepShouldHaveOptions(): bool
    {
        return in_array($this->getType(), self::STEPS_WITH_OPTIONS);
    }

    public function getOptions(): ?array
    {
        return $this->extract(self::KEY_OPTIONS);
    }

    public function hasTakingModifier(): bool
    {
        return $this->extract(self::KEY_TAKING_MODIFIER);
    }

    public function hasAnsweredModifier(): bool
    {
        return $this->extract(self::KEY_ANSWERED_MODIFIER);
    }

    protected function assertSpecificationIsValid(array $specification): void
    {
        if (!array_key_exists(self::KEY_NAME, $specification)) {
            throw InvalidStepSpecificationException::missingName();
        }

        if (!array_key_exists(self::KEY_TYPE, $specification)) {
            throw InvalidStepSpecificationException::missingType();
        }

        if (!array_key_exists(self::KEY_QUESTION, $specification)) {
            throw InvalidStepSpecificationException::missingQuestion();
        }
    }
}
