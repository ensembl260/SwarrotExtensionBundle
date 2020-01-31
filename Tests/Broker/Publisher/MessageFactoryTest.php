<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Publisher\MessageFactory;
use PHPUnit\Framework\TestCase;

class MessageFactoryTest extends TestCase
{
    /**
     * @var MessageFactory
     */
    private $messageFactory;

    public function setUp(): void
    {
        $this->messageFactory = new MessageFactory();
    }

    public function testSerialize(): void
    {
        $message = $this->messageFactory->createMessage('fake_data', ['fake_property'], 11);

        $this->assertEquals('fake_data', $message->getBody());
        $this->assertEquals(['fake_property'], $message->getProperties());
        $this->assertEquals(11, $message->getId());
    }
}
