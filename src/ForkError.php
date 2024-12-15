<?php

namespace Aikus\ForkManager;

class ForkError extends \Exception
{
    public function __construct(string $errorString, int $errorNumber)
    {
        parent::__construct($errorString, $errorNumber);
    }
}