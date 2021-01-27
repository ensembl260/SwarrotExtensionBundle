<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Exception;

class PublishException extends \Exception
{
    /** @var mixed */
    private $data;
    private string $messageType;

    /**
     * @param mixed $data
     */
    public function __construct(
        $data,
        string $messageType,
        ?string $message = null,
        int $code = 0,
        ?\Throwable $previous = null
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
