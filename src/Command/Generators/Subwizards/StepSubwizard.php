<?php

namespace Shomisha\LaravelConsoleWizard\Command\Generators\Subwizards;

use Shomisha\LaravelConsoleWizard\Command\Subwizard;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\Steps\ChoiceStep;
use Shomisha\LaravelConsoleWizard\Steps\ConfirmStep;
use Shomisha\LaravelConsoleWizard\Steps\MultipleAnswerTextStep;
use Shomisha\LaravelConsoleWizard\Steps\MultipleChoiceStep;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;
use Shomisha\LaravelConsoleWizard\Steps\UniqueMultipleChoiceStep;

class StepSubwizard extends Subwizard
{
    private $stepTypes = [
        'Text step' => TextStep::class,
        'Multiple answer text step' => MultipleAnswerTextStep::class,
        'Choice step' => ChoiceStep::class,
        'Multiple choice step' => MultipleChoiceStep::class,
        'Unique multiple choice step' => UniqueMultipleChoiceStep::class,
        'Confirm step' => ConfirmStep::class,
    ];

    private $stepSubwizards = [
        ChoiceStep::class => MultipleChoiceOptionsSubwizard::class,
        MultipleChoiceStep::class => MultipleChoiceOptionsSubwizard::class,
        UniqueMultipleChoiceStep::class => MultipleChoiceOptionsSubwizard::class,
    ];

    function getSteps(): array
    {
        return [
            'name' => new TextStep("Enter step name"),
            'question' => new TextStep("Enter step question"),
            'type' => new ChoiceStep("Choose step type", array_keys($this->stepTypes)),
        ];
    }

    public function answeredType(Step $step, string $type)
    {
        $type = $this->stepTypes[$type];

        if ($followUp = $this->guessFollowUp($type)) {
            $this->followUp(
                'step-data',
                $this->subWizard($followUp)
            );
        }

        return $type;
    }

    private function guessFollowUp(string $type): ?Subwizard
    {
        $followUpClass = $this->stepSubwizards[$type] ?? null;

        if ($followUpClass) {
            return new $followUpClass;
        }

        return null;
    }
}
