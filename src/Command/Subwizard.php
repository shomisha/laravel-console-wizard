<?php

namespace Shomisha\LaravelConsoleWizard\Command;

use Shomisha\LaravelConsoleWizard\Exception\SubwizardException;

abstract class Subwizard extends Wizard
{
    final function completed()
    {
        throw SubwizardException::completedMethodShouldNotBeCalled();
    }
}
