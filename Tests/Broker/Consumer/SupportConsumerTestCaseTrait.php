<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Consumer;

use MR\SwarrotExtensionBundle\Broker\Consumer\SupportConsumerInterface;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;

trait SupportConsumerTestCaseTrait
{
    /**
     * @var SupportConsumerInterface
     */
    protected $consumer;

    /**
     * @dataProvider notSupportDataProvider
     *
     * @param mixed $data
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
     *
     * @param mixed $data
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
