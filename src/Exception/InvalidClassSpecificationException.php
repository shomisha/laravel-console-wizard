<?php

namespace Shomisha\LaravelConsoleWizard\Exception;

class InvalidClassSpecificationException extends \Exception
{
    public static function missingName(): self
    {
        return self::missingParameter('name');
    }

    public static function missingSignature(): self
    {
        return self::missingParameter('signature');
    }

    private static function missingParameter(string $parameter): self
    {
        return new self("The class specification is missing a parameter: {$parameter}");
    }
}
