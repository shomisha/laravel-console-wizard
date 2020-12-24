<?php

namespace Shomisha\LaravelConsoleWizard\Exception;

class InvalidStepSpecificationException extends \Exception
{
    public static function missingName(): self
    {
        return self::missingParameter('name');
    }

    public static function missingType(): self
    {
        return self::missingParameter('type');
    }

    public static function missingQuestion(): self
    {
        return self::missingParameter('question');
    }

    private static function missingParameter(string $parameter): self
    {
        return new self("The step specification is missing a parameter: {$parameter}");
    }
}
