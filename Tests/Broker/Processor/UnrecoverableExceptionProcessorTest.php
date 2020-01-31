<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Processor;

use MR\SwarrotExtensionBundle\Broker\Exception\UnrecoverableConsumerException;
use MR\SwarrotExtensionBundle\Broker\Exception\UnrecoverableException;
use MR\SwarrotExtensionBundle\Broker\Processor\UnrecoverableExceptionProcessor;
use MR\SwarrotExtensionBundle\Broker\Publisher\ErrorPublisher;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use PHPUnit\Framework\TestCase;

class UnrecoverableExceptionProcessorTest extends TestCase
{
    /**
     * @var ProcessorInterface&ObjectProphecy
     */
    private $processorMock;

    /**
     * @var ErrorPublisher&ObjectProphecy
     */
    private $errorPublisherMock;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $loggerMock;

    /**
     * @var UnrecoverableExceptionProcessor
     */
    private $unrecoverableExceptionProcessor;

    public function setUp(): void
    {
        $this->processorMock = $this->prophesize(ProcessorInterface::class);
        $this->errorPublisherMock = $this->prophesize(ErrorPublisher::class);
        $this->loggerMock = $this->prophesize(LoggerInterface::class);
        $this->unrecoverableExceptionProcessor = new UnrecoverableExceptionProcessor(
            $this->processorMock->reveal(),
            $this->errorPublisherMock->reveal(),
            $this->loggerMock->reveal()
        );
    }

    public function testProcessWillThrowException(): void
    {
        $options = [];
        $message = new Message();
        $exception = new \Exception('My fake exception message.');
        $this->processorMock
            ->process($message, $options)
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('My fake exception message.');

        $this->unrecoverableExceptionProcessor->process($message, $options);
    }

    public function testProcessWillThrowUnrecoverableException(): void
    {
        $options = [];
        $message = new Message();
        $exception = new UnrecoverableException();
        $this->processorMock
            ->process($message, $options)
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->critical(
                '[UnrecoverableExceptionProcessor] An UnrecoverableException occurred.',
                [
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'unrecoverable_exception_processor',
                ]
            )
            ->shouldBeCalled();

        $this->errorPublisherMock->exception($exception)->shouldBeCalled();

        $this->assertNull($this->unrecoverableExceptionProcessor->process($message, $options));
    }

    public function testProcessWillReThrowUnrecoverableConsumerException(): void
    {
        $options = [];
        $message = new Message();
        $exception = new UnrecoverableConsumerException($message, null, null, true);
        $this->processorMock
            ->process($message, $options)
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->critical(
                '[UnrecoverableExceptionProcessor] An UnrecoverableException occurred.',
                [
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'unrecoverable_exception_processor',
                ]
            )
            ->shouldBeCalled();

        $this->errorPublisherMock->exception($exception)->shouldBeCalled();

        $this->loggerMock
            ->critical(
                '[UnrecoverableExceptionProcessor] Gonna to rethrow the exception.',
                [
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'unrecoverable_exception_processor',
                ]
            )
            ->shouldBeCalled();

        $this->expectException(UnrecoverableConsumerException::class);
        $this->expectExceptionMessage('Unrecoverable exception occured.');

        $this->unrecoverableExceptionProcessor->process($message, $options);
    }

    public function testProcessWillLetsRunConsumer(): void
    {
        $options = [];
        $message = new Message();
        $exception = new UnrecoverableConsumerException($message);
        $this->processorMock
            ->process($message, $options)
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->critical(
                '[UnrecoverableExceptionProcessor] An UnrecoverableException occurred.',
                [
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'unrecoverable_exception_processor',
                ]
            )
            ->shouldBeCalled();

        $this->errorPublisherMock->exception($exception)->shouldBeCalled();

        $this->loggerMock
            ->critical(
                '[UnrecoverableExceptionProcessor] Gonna to let\'s run consumer.',
                [
                    'killConsumer' => false,
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'unrecoverable_exception_processor',
                ]
            )
            ->shouldBeCalled();

        $this->assertTrue($this->unrecoverableExceptionProcessor->process($message, $options));
    }

    public function testProcessWillKillConsumer(): void
    {
        $options = [];
        $message = new Message();
        $exception = new UnrecoverableConsumerException($message, null, null, false, true);
        $this->processorMock
            ->process($message, $options)
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->critical(
                '[UnrecoverableExceptionProcessor] An UnrecoverableException occurred.',
                [
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'unrecoverable_exception_processor',
                ]
            )
            ->shouldBeCalled();

        $this->errorPublisherMock->exception($exception)->shouldBeCalled();

        $this->loggerMock
            ->critical(
                '[UnrecoverableExceptionProcessor] Gonna to kill consumer.',
                [
                    'killConsumer' => true,
                    'exception' => $exception,
                    'message_id' => $message->getId(),
                    'swarrot_processor' => 'unrecoverable_exception_processor',
                ]
            )
            ->shouldBeCalled();

        $this->assertFalse($this->unrecoverableExceptionProcessor->process($message, $options));
    }
}
