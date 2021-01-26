<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Publisher;

use Exception;
use MR\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;
use MR\SwarrotExtensionBundle\Broker\Publisher\ErrorPublisher;
use MR\SwarrotExtensionBundle\Broker\Publisher\PublisherInterface;
use MR\SwarrotExtensionBundle\Broker\Publisher\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Swarrot\Broker\Message;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class ErrorPublisherTest extends TestCase
{
    /**
     * @var ObjectProphecy|PublisherInterface
     */
    private ObjectProphecy $publisherMock;

    /**
     * @var ObjectProphecy|SerializerInterface
     */
    private ObjectProphecy $serializerMock;

    private ErrorPublisher $errorPublisher;

    public function setUp(): void
    {
        $this->publisherMock = $this->prophesize(PublisherInterface::class);
        $this->serializerMock = $this->prophesize(SerializerInterface::class);

        $this->errorPublisher = new ErrorPublisher(
            $this->publisherMock->reveal(),
            'my_fake_%s_routing_key',
            $this->serializerMock->reveal()
        );
    }

    public function testErrorPublish(): void
    {
        $exception = new Exception('my_fake_message_exception');
        $message = new Message('my_fake_body', ['my_fake_properties'], '123');
        $xDeathEvent = new XDeathEvent('xdeath.max_lifetime_reached', $exception, $message, ['fake_options']);
        $exceptionTrace = FlattenException::create($exception)->toArray();

        $this->serializerMock
            ->serialize(
                [
                    'data' => [
                        'reason' => 'Rabbit MQ Fail',
                        'exception' => reset($exceptionTrace),
                        'hostname' => gethostname(),
                    ],
                    'metadata' => [
                        'rabbit' => [
                            'xdeath-event-type' => 'xdeath.max_lifetime_reached',
                            'options' => ['fake_options'],
                            'message' => [
                                'id' => 123,
                                'body' => 'my_fake_body',
                                'properties' => ['my_fake_properties'],
                            ],
                        ],
                    ],
                ],
                'json'
            )
            ->willReturn('fake_data_serialized')
            ->shouldBeCalled();

        $this->publisherMock
            ->publish(
                'error',
                'fake_data_serialized',
                ['content_type' => 'application/json'],
                ['routing_key' => 'my_fake_error.rabbit.xdeath_routing_key',]
            )
            ->shouldBeCalled();

        $this->errorPublisher->xdeathEvent($xDeathEvent);
    }

    public function testErrorPublishThrowable(): void
    {
        $exception = new Exception('my_fake_message_exception');
        $message = new Message('my_fake_body', ['my_fake_properties'], '123');
        $xDeathEvent = new XDeathEvent('xdeath.max_lifetime_reached', $exception, $message, ['fake_options']);
        $exceptionTrace = FlattenException::create($exception)->toArray();

        $this->serializerMock
            ->serialize(
                [
                    'data' => [
                        'reason' => 'Rabbit MQ Fail',
                        'exception' => reset($exceptionTrace),
                        'hostname' => gethostname(),
                    ],
                    'metadata' => [
                        'rabbit' => [
                            'xdeath-event-type' => 'xdeath.max_lifetime_reached',
                            'options' => ['fake_options'],
                            'message' => [
                                'id' => 123,
                                'body' => 'my_fake_body',
                                'properties' => ['my_fake_properties'],
                            ],
                        ],
                    ],
                ],
                'json'
            )
            ->willReturn('fake_data_serialized')
            ->shouldBeCalled();

        $this->publisherMock
            ->publish(
                'error',
                'fake_data_serialized',
                ['content_type' => 'application/json'],
                ['routing_key' => 'my_fake_error.rabbit.xdeath_routing_key',]
            )
            ->shouldBeCalled();

        $this->errorPublisher->xdeathEvent($xDeathEvent);
    }

    public function testOtherException(): void
    {
        $exception = new \Exception('An error occured');
        $exceptionTrace = FlattenException::create($exception)->toArray();

        $this->serializerMock
            ->serialize(
                [
                    'data' => [
                        'reason' => 'An error occured',
                        'exception' => reset($exceptionTrace),
                        'hostname' => gethostname(),
                    ],
                    'metadata' => [],
                ],
                'json'
            )
            ->willReturn('fake_data_serialized')
            ->shouldBeCalled();

        $this->publisherMock
            ->publish(
                'error',
                'fake_data_serialized',
                ['content_type' => 'application/json'],
                ['routing_key' => 'my_fake_error.exception_routing_key',]
            )
            ->shouldBeCalled();

        $this->errorPublisher->exception($exception);
    }
}
