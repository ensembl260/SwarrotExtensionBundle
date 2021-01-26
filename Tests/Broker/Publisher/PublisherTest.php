<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\PublishException;
use MR\SwarrotExtensionBundle\Broker\Publisher\MessageFactoryInterface;
use MR\SwarrotExtensionBundle\Broker\Publisher\Publisher;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\SwarrotBundle\Broker\Publisher as SwarrotPublisher;

class PublisherTest extends TestCase
{
    private Publisher $publisher;

    /**
     * @var SwarrotPublisher&ObjectProphecy
     */
    private ObjectProphecy $swarrotPublisherMock;

    /**
     * @var MessageFactoryInterface&ObjectProphecy
     */
    private ObjectProphecy $messageFactoryMock;

    /**
     * @var LoggerInterface&ObjectProphecy
     */
    private ObjectProphecy $loggerMock;

    public function setUp(): void
    {
        $this->swarrotPublisherMock = $this->prophesize(SwarrotPublisher::class);
        $this->messageFactoryMock = $this->prophesize(MessageFactoryInterface::class);
        $this->loggerMock = $this->prophesize(LoggerInterface::class);

        $this->publisher = new Publisher(
            $this->swarrotPublisherMock->reveal(),
            $this->messageFactoryMock->reveal()
        );

        $this->publisher->setLogger($this->loggerMock->reveal());
    }

    public function testPublish(): void
    {
        $data = [
            'data' => [
                'id' => 123,
            ],
        ];
        $config = [
            'connection' => 'default.connection',
            'exchange' => 'default.exchange',
            'routing_key' => 'default.routing.key',
            'basic_config' => 'some_value',
        ];
        $overridenConfig = [
            'connection' => 'overriden.connection',
            'exchange' => 'overriden.exchange',
            'routing_key' => 'overriden.routing.key',
            'extra_config' => 'other_value',
        ];
        $expectedConfig = [
            'connection' => 'overriden.connection',
            'exchange' => 'overriden.exchange',
            'routing_key' => 'overriden.routing.key',
            'basic_config' => 'some_value',
            'extra_config' => 'other_value',
        ];
        $properties = [];

        $message = new Message();

        $this->messageFactoryMock
            ->createMessage($data, ['headers' => ['X-Routing-key' => 'overriden.routing.key']])
            ->willReturn($message)
            ->shouldBeCalled();

        $this->swarrotPublisherMock->getConfigForMessageType('message_type')->willReturn($config);
        $this->swarrotPublisherMock
            ->publish('message_type', $message, $expectedConfig)
            ->shouldBeCalledTimes(1);

        $this->loggerMock
            ->info(
                'Publish success.',
                [
                    'data' => $data,
                    'message_type' => 'message_type',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\Publisher',
                    'line' => 54,
                ]
            )
            ->shouldBeCalled();

        $this->publisher->publish('message_type', $data, $properties, $overridenConfig);
    }

    public function testPublishWillThrowPublishException(): void
    {
        $data = [
            'data' => [
                'id' => 123,
            ],
        ];
        $config = [
            'connection' => 'default.connection',
            'exchange' => 'default.exchange',
            'routing_key' => 'default.routing.key',
            'basic_config' => 'some_value',
        ];
        $overridenConfig = [
            'connection' => 'overriden.connection',
            'exchange' => 'overriden.exchange',
            'routing_key' => 'overriden.routing.key',
            'extra_config' => 'other_value',
        ];
        $expectedConfig = [
            'connection' => 'overriden.connection',
            'exchange' => 'overriden.exchange',
            'routing_key' => 'overriden.routing.key',
            'basic_config' => 'some_value',
            'extra_config' => 'other_value',
        ];
        $properties = [];

        $message = new Message();

        $this->messageFactoryMock
            ->createMessage($data, ['headers' => ['X-Routing-key' => 'overriden.routing.key']])
            ->willReturn($message)
            ->shouldBeCalled();

        $exception = new \Exception('my_fake_exception_message');
        $this->swarrotPublisherMock->getConfigForMessageType('message_type')->willReturn($config);
        $this->swarrotPublisherMock
            ->publish('message_type', $message, $expectedConfig)
            ->willThrow($exception);

        $this->loggerMock
            ->error(
                'Publish fail.',
                [
                    'exception' => $exception,
                    'data' => $data,
                    'message_type' => 'message_type',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\Publisher',
                    'line' => 69,
                ]
            )
            ->shouldBeCalled();

        $this->expectException(PublishException::class);
        $this->expectExceptionMessage('Publish fail.');

        $this->publisher->publish('message_type', $data, $properties, $overridenConfig);
    }
}
