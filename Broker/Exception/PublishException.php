<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Exception;

class PublishException extends \Exception
{
    private string $object;
    private string $messageType;

    public function __construct(
        string $object,
        string $messageType,
        ?string $message = null,
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->object = $object;
        $this->messageType = $messageType;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }
}
