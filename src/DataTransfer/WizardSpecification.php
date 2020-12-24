<?php

namespace Shomisha\LaravelConsoleWizard\DataTransfer;

use Shomisha\LaravelConsoleWizard\Command\GeneratorWizard;
use Shomisha\LaravelConsoleWizard\Exception\InvalidClassSpecificationException;

class WizardSpecification extends Specification
{
    const KEY_NAMESPACE = 'namespace';
    const KEY_CLASS_NAME = GeneratorWizard::NAME_STEP_NAME;
    const KEY_SIGNATURE = 'signature';
    const KEY_DESCRIPTION = 'description';

    private $stepSpecifications;

    public function __construct(array $specification)
    {
        parent::__construct($specification);

        $this->initializeSteps($this->extract('steps'));
    }

    public function getName(): string
    {
        return $this->extract(self::KEY_CLASS_NAME);
    }

    public function setName(string $name): self
    {
        return $this->place(self::KEY_CLASS_NAME, $name);
    }

    public function getSignature(): string
    {
        return $this->extract(self::KEY_SIGNATURE);
    }

    public function getDescription(): ?string
    {
        return $this->extract(self::KEY_DESCRIPTION);
    }

    /** @return \Shomisha\LaravelConsoleWizard\DataTransfer\StepSpecification[] */
    public function getSteps(): array
    {
        return $this->stepSpecifications;
    }

    public function getNamespace(): ?string
    {
        return $this->extract(self::KEY_NAMESPACE);
    }

    public function setNamespace(?string $namespace): self
    {
        return $this->place(self::KEY_NAMESPACE, $namespace);
    }

    protected function assertSpecificationIsValid(array $specification): void
    {
        if (!array_key_exists(self::KEY_CLASS_NAME, $specification)) {
            InvalidClassSpecificationException::missingName();
        }

        if (!array_key_exists(self::KEY_SIGNATURE, $specification)) {
            InvalidClassSpecificationException::missingSignature();
        }
    }

    private function initializeSteps(array $steps): void
    {
        $this->stepSpecifications = collect($steps)->mapWithKeys(function (array $step) {
            $specification = StepSpecification::fromArray($step);

            return [
                $specification->getName() => $specification
            ];
        })->toArray();
    }
}
