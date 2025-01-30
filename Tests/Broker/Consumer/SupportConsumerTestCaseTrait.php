<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Tests\Broker\Consumer;

use Ensembl260\SwarrotExtensionBundle\Broker\Consumer\SupportConsumerInterface;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;

trait SupportConsumerTestCaseTrait
{
    /**
     * @dataProvider notSupportDataProvider
     */
    public function testNotSupportedMessage($data): void
    {
        if (!$this->consumer instanceof SupportConsumerInterface) {
            TestCase::markTestSkipped('Only for consumer that implements SupportConsumerInterface.');
        }

        TestCase::assertFalse($this->consumer->supportData($data, new Message(), []));
    }

    /**
     * @dataProvider supportDataProvider
     */
    public function testSupportedMessage($data): void
    {
        if (!$this->consumer instanceof SupportConsumerInterface) {
            TestCase::markTestSkipped('Only for consumer that implements SupportConsumerInterface.');
        }

        TestCase::assertTrue($this->consumer->supportData($data, new Message(), []));
    }

    abstract public function notSupportDataProvider(): array;

    abstract public function supportDataProvider(): array;
}
