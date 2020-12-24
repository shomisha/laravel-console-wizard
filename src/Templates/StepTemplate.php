<?php

namespace Shomisha\LaravelConsoleWizard\Templates;

use Shomisha\LaravelConsoleWizard\DataTransfer\StepSpecification;
use Shomisha\Stubless\ImperativeCode\InstantiateBlock;
use Shomisha\Stubless\Utilities\Importable;
use Shomisha\Stubless\Values\Value;

class StepTemplate extends InstantiateBlock
{
    public function __construct(StepSpecification $specification)
    {
        $class = new Importable($specification->getType());

        $arguments = [
            Value::string($specification->getQuestion()),
        ];

        if ($specification->hasOptions()) {
            $arguments[] = Value::array($specification->getOptions());
        }

        return parent::__construct($class, $arguments);
    }

    public static function bySpecification(StepSpecification $specification): self
    {
        return new self($specification);
    }
}
