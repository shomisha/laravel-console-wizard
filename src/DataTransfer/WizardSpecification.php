<?php

namespace Shomisha\LaravelConsoleWizard\DataTransfer;

use Shomisha\LaravelConsoleWizard\Exception\InvalidClassSpecificationException;

class WizardSpecification
{
    const KEY_CLASS_NAME = 'name';
    const KEY_SIGNATURE = 'signature';
    const KEY_DESCRIPTION = 'description';

    private $specification;

    public function __construct(array $specification)
    {
        $this->assertSpecificationIsValid($specification);

        $this->specification = $specification;
    }

    public static function fromArray(array $specification): self
    {
        return new self($specification);
    }

    public function getName(): string
    {
        return $this->specification[self::KEY_CLASS_NAME];
    }

    public function getSignature(): string
    {
        return $this->specification[self::KEY_SIGNATURE];
    }

    public function getDescription(): ?string
    {
        return $this->specification[self::KEY_DESCRIPTION] ?? null;
    }

    private function assertSpecificationIsValid(array $specification): void
    {
        if (!array_key_exists(self::KEY_CLASS_NAME, $specification)) {
            InvalidClassSpecificationException::missingName();
        }

        if (!array_key_exists(self::KEY_SIGNATURE, $specification)) {
            InvalidClassSpecificationException::missingSignature();
        }
    }
}
