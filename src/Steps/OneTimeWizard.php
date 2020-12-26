<?php

namespace Shomisha\LaravelConsoleWizard\Steps;

use Shomisha\LaravelConsoleWizard\Command\Wizard;

class OneTimeWizard extends Wizard
{
    private array $multiValueSteps;

    public function __construct(array $steps)
    {
        parent::__construct();

        $this->assertStepsAreValid($steps);

        $this->multiValueSteps = $steps;
    }

    function getSteps(): array
    {
        return $this->multiValueSteps;
    }

    function completed()
    {
        throw new \RuntimeException('One time wizard cannot reach the completed method.');
    }
}
