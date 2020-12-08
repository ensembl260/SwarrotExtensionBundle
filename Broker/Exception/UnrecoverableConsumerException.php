<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Exception;

use Swarrot\Broker\Message;

class UnrecoverableConsumerException extends UnrecoverableException
{
    private Message $brokerMessage;
    private bool $rethrow;
    private bool $killConsumer;

    public function __construct(
        Message $brokerMessage,
        string $message = null,
        \Throwable $previous = null,
        bool $rethrow = false,
        bool $killConsumer = false
    ) {
        parent::__construct($message, 0, $previous);

        $this->brokerMessage = $brokerMessage;
        $this->rethrow = $rethrow;
        $this->killConsumer = $killConsumer;
    }

    public function getBrokerMessage(): Message
    {
        return $this->brokerMessage;
    }

    public function wantRethrow(): bool
    {
        return $this->rethrow;
    }

    public function wantKillConsumer(): bool
    {
        return $this->killConsumer;
    }
}
