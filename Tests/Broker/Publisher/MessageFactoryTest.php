<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Tests\Broker\Publisher;

use Ensembl260\SwarrotExtensionBundle\Broker\Publisher\MessageFactory;
use PHPUnit\Framework\TestCase;

class MessageFactoryTest extends TestCase
{
    private MessageFactory $messageFactory;

    public function setUp(): void
    {
        $this->messageFactory = new MessageFactory();
    }

    public function testSerialize(): void
    {
        $message = $this->messageFactory->createMessage('fake_data', ['fake_property'], '11');

        self::assertEquals('fake_data', $message->getBody());
        self::assertEquals(['fake_property'], $message->getProperties());
        self::assertEquals(11, $message->getId());
    }
}
