<?php

namespace Shomisha\LaravelConsoleWizard\Exception;

use Throwable;

class AbortWizardException extends \Exception
{
    protected $message = "Wizard abortion initiated by client.";

    private $userMessage = null;

    public function __construct(string $userMessage = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct(null, $code, $previous);

        $this->userMessage = $userMessage;
    }

    public function getUserMessage()
    {
        return $this->userMessage;
    }
}
