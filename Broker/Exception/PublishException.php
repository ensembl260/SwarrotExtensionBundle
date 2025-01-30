<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Exception;

class PublishException extends \Exception
{
    private $data;
    private string $messageType;

    public function __construct(
        $data,
        string $messageType,
        ?string $message = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);

        $this->data = $data;
        $this->messageType = $messageType;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }
}
