<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\PublishException;
use MR\SwarrotExtensionBundle\Broker\Publisher\MessageFactoryInterface;
use MR\SwarrotExtensionBundle\Broker\Publisher\SpooledPublisher;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\SwarrotBundle\Broker\Publisher as SwarrotPublisher;
use PHPUnit\Framework\TestCase;

class SpooledPublisherTest extends TestCase
{
    /**
     * @var SpooledPublisher
     */
    private $spooledPublisher;

    /**
     * @var SwarrotPublisher&ObjectProphecy
     */
    private $swarrotPublisherMock;

    /**
     * @var MessageFactoryInterface&ObjectProphecy
     */
    private $messageFactoryMock;

    /**
     * @var LoggerInterface&ObjectProphecy
     */
    private $loggerMock;

    public function setUp(): void
    {
        $this->swarrotPublisherMock = $this->prophesize(SwarrotPublisher::class);
        $this->messageFactoryMock = $this->prophesize(MessageFactoryInterface::class);
        $this->loggerMock = $this->prophesize(LoggerInterface::class);

        $this->spooledPublisher = new SpooledPublisher(
            $this->swarrotPublisherMock->reveal(),
            $this->messageFactoryMock->reveal()
        );

        $this->spooledPublisher->setLogger($this->loggerMock->reveal());
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

        $this->loggerMock
            ->debug(
                'Message spooled.',
                [
                    'data' => $data,
                    'message_type' => 'message_type',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\SpooledPublisher',
                    'line' => 61,
                ]
            )
            ->shouldBeCalled();

        $this->swarrotPublisherMock
            ->publish()
            ->shouldNotBeCalled();

        $this->spooledPublisher->publish('message_type', $data, $properties, $overridenConfig);
    }

    public function testFlush(): void
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
        $this->loggerMock
            ->debug(
                'Message spooled.',
                [
                    'data' => $data,
                    'message_type' => 'message_type',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\SpooledPublisher',
                    'line' => 61,
                ]
            )
            ->shouldBeCalled();

        $this->swarrotPublisherMock
            ->publish('message_type', $message, $expectedConfig)
            ->shouldBeCalledTimes(1);

        $this->loggerMock
            ->info(
                'Spooled message published successfully.',
                [
                    'data' => $message,
                    'message_type' => 'message_type',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\SpooledPublisher',
                    'line' => 69,
                ]
            )
            ->shouldBeCalled();

        $this->spooledPublisher->publish('message_type', $data, $properties, $overridenConfig);
        $this->spooledPublisher->flush();
    }

    public function testFlushWillThrowPublishException(): void
    {
        $data = [
            'data' => [
                'id' => 123,
            ],
        ];
        $data2 = [
            'data' => [
                'id' => 456,
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
        $message2 = new Message();

        $this->messageFactoryMock
            ->createMessage($data, ['headers' => ['X-Routing-key' => 'overriden.routing.key']])
            ->willReturn($message)
            ->shouldBeCalled();

        $this->swarrotPublisherMock
            ->getConfigForMessageType('message_type')
            ->willReturn($config)
            ->shouldBeCalled();

        $this->messageFactoryMock
            ->createMessage($data2, ['headers' => ['X-Routing-key' => 'overriden.routing.key']])
            ->willReturn($message2)
            ->shouldBeCalled();

        $this->swarrotPublisherMock
            ->getConfigForMessageType('message_type2')
            ->willReturn($config)
            ->shouldBeCalled();

        $this->loggerMock
            ->debug(
                'Message spooled.',
                [
                    'data' => $data,
                    'message_type' => 'message_type',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\SpooledPublisher',
                    'line' => 61,
                ]
            )
            ->shouldBeCalled();
        $this->loggerMock
            ->debug(
                'Message spooled.',
                [
                    'data' => $data2,
                    'message_type' => 'message_type2',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\SpooledPublisher',
                    'line' => 61,
                ]
            )
            ->shouldBeCalled();

        $exception = new \Exception('my_fake_exception');

        $this->swarrotPublisherMock
            ->publish('message_type', $message, $expectedConfig)
            ->willThrow($exception)
            ->shouldBeCalledTimes(1);

        $this->loggerMock
            ->error(
                'Spooled message publish fail.',
                [
                    'exception' => $exception,
                    'data' => $message,
                    'message_type' => 'message_type',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\SpooledPublisher',
                    'line' => 75,
                ]
            )
            ->shouldBeCalled();

        $this->loggerMock
            ->debug(
                'Spooled message not flushed.',
                [
                    'data' => $message2,
                    'message_type' => 'message_type2',
                    'connection' => $expectedConfig['connection'],
                    'exchange' => $expectedConfig['exchange'],
                    'routing_key' => $expectedConfig['routing_key'],
                    'class' => 'MR\SwarrotExtensionBundle\Broker\Publisher\SpooledPublisher',
                    'line' => 77,
                ]
            )
            ->shouldBeCalled();

        $this->spooledPublisher->publish('message_type', $data, $properties, $overridenConfig);
        $this->spooledPublisher->publish('message_type2', $data2, $properties, $overridenConfig);

        $this->expectException(PublishException::class);
        $this->expectExceptionMessage('Spooled message publish fail.');

        $this->spooledPublisher->flush();
    }

    public function testClear(): void
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
        $properties = [];

        $message = new Message();

        $this->messageFactoryMock
            ->createMessage($data, ['headers' => ['X-Routing-key' => 'overriden.routing.key']])
            ->willReturn($message)
            ->shouldBeCalled();

        $this->swarrotPublisherMock
            ->getConfigForMessageType('message_type')
            ->willReturn($config)
            ->shouldBeCalled();

        $this->spooledPublisher->publish('message_type', $data, $properties, $overridenConfig);

        $reflection = new \ReflectionClass(SpooledPublisher::class);
        $reflectionWaitingCalls = $reflection->getProperty('waitingCalls');
        $reflectionWaitingCalls->setAccessible(true);

        $this->assertEquals(
            [
                [
                    'message_type',
                    $message,
                    [
                        'connection' => $overridenConfig['connection'],
                        'exchange' => $overridenConfig['exchange'],
                        'routing_key' => $overridenConfig['routing_key'],
                        'basic_config' => 'some_value',
                        'extra_config' => 'other_value',
                    ]
                ]
            ],
            $reflectionWaitingCalls->getValue($this->spooledPublisher)
        );

        $this->spooledPublisher->clear();

        $this->assertEquals([], $reflectionWaitingCalls->getValue($this->spooledPublisher));
    }
}
