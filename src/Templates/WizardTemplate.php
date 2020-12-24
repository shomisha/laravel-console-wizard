<?php

namespace Shomisha\LaravelConsoleWizard\Templates;

use Illuminate\Support\Str;
use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\Contracts\Step;
use Shomisha\LaravelConsoleWizard\DataTransfer\StepSpecification;
use Shomisha\LaravelConsoleWizard\DataTransfer\WizardSpecification;
use Shomisha\Stubless\DeclarativeCode\Argument;
use Shomisha\Stubless\ImperativeCode\Block;
use Shomisha\Stubless\DeclarativeCode\ClassMethod;
use Shomisha\Stubless\DeclarativeCode\ClassProperty;
use Shomisha\Stubless\DeclarativeCode\ClassTemplate;
use Shomisha\Stubless\References\Reference;
use Shomisha\Stubless\Utilities\Importable;
use Shomisha\Stubless\Values\Value;

class WizardTemplate extends ClassTemplate
{
    public function __construct(WizardSpecification $specification)
    {
        $name = $specification->getName();

        parent::__construct($name);

        $this->initialize($specification);
    }

    public static function bySpecification(WizardSpecification $specification): self
    {
        return new self($specification);
    }

    private function initialize(WizardSpecification $specification): void
    {
        $this->setNamespace($specification->getNamespace());

        $this->extends(new Importable(Wizard::class));

        $this->addProperty(
            ClassProperty::name('signature')->value($specification->getSignature())->makeProtected()
        );

        if ($description = $specification->getDescription()) {
            $this->addProperty(
                ClassProperty::name('description')->value($description)->makeProtected()
            );
        }

        $this->initializeSteps($specification);

        $this->addMethod(
            $this->getCompletedMethod()
        );
    }

    private function initializeSteps(WizardSpecification $specification): void
    {
        $this->addMethod(
            $method = ClassMethod::name('getSteps')->return('array')
        );

        $steps = array_map(function (StepSpecification $stepSpecification) {
            $stepTemplate = StepTemplate::bySpecification($stepSpecification);

            if ($stepSpecification->hasTakingModifier()) {
                $this->addMethod(
                    $this->createTakingModifier($stepSpecification)
                );
            }

            if ($stepSpecification->hasAnsweredModifier()) {
                $this->addMethod(
                    $this->createAnsweredModifier($stepSpecification)
                );
            }

            return $stepTemplate;
        }, $specification->getSteps());

        $method->body(
            Block::return(Value::array($steps))
        );
    }

    private function createTakingModifier(StepSpecification $specification): ClassMethod
    {
        $stepName = "taking" . Str::studly($specification->getName());

        return ClassMethod::name($stepName)->addArgument(
            Argument::name('step')->type(new Importable(Step::class))
        );
    }

    private function createAnsweredModifier(StepSpecification $specification): ClassMethod
    {
        $stepName = "answered" . Str::studly($specification->getName());
        $argumentName = Str::camel($specification->getName());

        return ClassMethod::name($stepName)->withArguments([
            Argument::name('step')->type(new Importable(Step::class)),
            Argument::name($argumentName)
        ])->body(
            Block::return(Reference::variable($argumentName))
        );
    }

    private function getCompletedMethod(): ClassMethod
    {
        return ClassMethod::name('completed')->body(
            Block::return(
                Block::invokeMethod(
                    Reference::objectProperty(Reference::this(), 'answers'),
                    'all'
                )
            )
        );
    }
}
