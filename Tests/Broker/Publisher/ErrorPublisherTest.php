<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\UnrecoverableConsumerException;
use MR\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;
use MR\SwarrotExtensionBundle\Broker\Publisher\ErrorPublisher;
use MR\SwarrotExtensionBundle\Broker\Publisher\PublisherInterface;
use MR\SwarrotExtensionBundle\Broker\Publisher\SerializerInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Prophecy\Prophecy\ObjectProphecy;
use Swarrot\Broker\Message;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FlattenException;
use PHPUnit\Framework\TestCase;

class ErrorPublisherTest extends TestCase
{
    /**
     * @var PublisherInterface&ObjectProphecy
     */
    private $publisherMock;

    /**
     * @var SerializerInterface
     */
    private $serializerMock;

    /**
     * @var ErrorPublisher
     */
    private $errorPublisher;

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
        $exception = new \Exception('my_fake_message_exception');
        $message = new Message('my_fake_body', ['my_fake_properties'], 123);
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
        $exception = new \Error('my_fake_message_exception');
        $message = new Message('my_fake_body', ['my_fake_properties'], 123);
        $xDeathEvent = new XDeathEvent('xdeath.max_lifetime_reached', $exception, $message, ['fake_options']);
        $exceptionTrace = FlattenException::create(new FatalThrowableError($exception))->toArray();

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

    public function testUnrecoverableException(): void
    {
        $message = new Message('my_fake_body', ['my_fake_properties'], 123);
        $unrecoverableException = new UnrecoverableConsumerException($message, 'My fake exception message.', null, true, true);
        $exceptionTrace = FlattenException::create($unrecoverableException)->toArray();

        $this->serializerMock
            ->serialize(
                [
                    'data' => [
                        'reason' => 'My fake exception message.',
                        'exception' => reset($exceptionTrace),
                        'hostname' => gethostname(),
                    ],
                    'metadata' => [
                        'rabbit' => [
                            'want-kill-consumer' => true,
                            'want-rethrow' => true,
                            'message' => [
                                'id' => $message->getId(),
                                'body' => $message->getBody(),
                                'properties' => $message->getProperties(),
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
                ['routing_key' => 'my_fake_error.unrecoverable_exception_routing_key',]
            )
            ->shouldBeCalled();

        $this->errorPublisher->exception($unrecoverableException);
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
