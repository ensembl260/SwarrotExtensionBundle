<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Exception;

class UnrecoverableException extends \Exception
{
    public function __construct($message = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: 'Unrecoverable exception occured.', $code, $previous);
    }
}
