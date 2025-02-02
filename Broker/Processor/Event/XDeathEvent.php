<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Broker\Processor\Event;

use Swarrot\Broker\Message;
use Symfony\Contracts\EventDispatcher\Event;

final class XDeathEvent extends Event
{
    public const MAX_LIFETIME_REACHED = 'xdeath.max_lifetime_reached';
    public const MAX_COUNT_REACHED = 'xdeath.max_count_reached';

    private string $type;
    private \Throwable $exception;
    private Message $message;

    /** @var array|mixed[] */
    private array $options;

    /**
     * @param array|mixed[] $options
     */
    public function __construct(string $type, \Throwable $exception, Message $message, array $options)
    {
        if (!in_array($type, [static::MAX_LIFETIME_REACHED, static::MAX_COUNT_REACHED], true)) {
            throw new \InvalidArgumentException('Invalid xdeath event type.');
        }

        $this->type = $type;
        $this->exception = $exception;
        $this->message = $message;
        $this->options = $options;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
