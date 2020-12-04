<?php

namespace Shomisha\LaravelConsoleWizard\Templates;

use Shomisha\LaravelConsoleWizard\Command\Wizard;
use Shomisha\LaravelConsoleWizard\DataTransfer\WizardSpecification;
use Shomisha\Stubless\Templates\ClassMethod;
use Shomisha\Stubless\Templates\ClassProperty;
use Shomisha\Stubless\Templates\ClassTemplate;
use Shomisha\Stubless\Utilities\Importable;

class WizardTemplate extends ClassTemplate
{
    public function __construct(WizardSpecification $specification)
    {
        $name = $specification->getName();
        $extends = new Importable(Wizard::class);

        parent::__construct($name, $extends);

        $this->initialize($specification);
    }

    public static function bySpecification(WizardSpecification $specification): self
    {
        return new self($specification);
    }

    private function initialize(WizardSpecification $specification): void
    {
        $this->addProperty(
            ClassProperty::name('signature')->value($specification->getSignature())->makeProtected()
        );

        if ($description = $specification->getDescription()) {
            $this->addProperty(
                ClassProperty::name('description')->value($description)->makeProtected()
            );
        }

        $this->initializeSteps($specification);
    }

    private function initializeSteps(WizardSpecification $specification): void
    {
        $this->addMethod(
            $method = ClassMethod::name('getSteps')->return('array')
        );
    }
}
