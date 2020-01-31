<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Processor;

use MR\SwarrotExtensionBundle\Broker\Consumer\ConstraintConsumerInterface;
use MR\SwarrotExtensionBundle\Broker\Consumer\ConsumerInterface;
use MR\SwarrotExtensionBundle\Broker\Consumer\SupportConsumerInterface;
use MR\SwarrotExtensionBundle\Broker\Exception\InvalidDataException;
use MR\SwarrotExtensionBundle\Broker\Processor\Processor;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessorTest extends TestCase
{
    /**
     * @var Message
     */
    private $message;

    /**
     * @var ConsumerInterface&SupportConsumerInterface&ConstraintConsumerInterface&ObjectProphecy
     */
    private $consumerMock;

    /**
     * @var string
     */
    private $consumerClass;

    /**
     * @var ValidatorInterface&ObjectProphecy
     */
    private $validatorMock;

    /**
     * @var LoggerInterface&ObjectProphecy
     */
    private $loggerMock;

    /**
     * @var Processor
     */
    private $processor;

    public function setUp(): void
    {
        $this->message = new Message('my_fake_body', [], 123);

        $this->consumerMock = $this->prophesize(ConsumerInterface::class);
        $this->consumerMock->willImplement(SupportConsumerInterface::class);
        $this->consumerMock->willImplement(ConstraintConsumerInterface::class);
        $consumerMock = $this->consumerMock->reveal();
        $this->consumerClass = get_class($consumerMock);

        $this->validatorMock = $this->prophesize(ValidatorInterface::class);

        $this->loggerMock = $this->prophesize(LoggerInterface::class);
        $this->loggerMock
            ->info('Start consuming message #{message_id}.', ['message_id' => 123, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->processor = new Processor(
            $consumerMock,
            $this->validatorMock->reveal()
        );
        $this->processor->setLogger($this->loggerMock->reveal());
    }

    public function testGetDataWillThrowInvalidDataException(): void
    {
        $exception = new InvalidDataException('my_fake_message');
        $this->consumerMock
            ->getData($this->message, [])
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->error('my_fake_message', ['exception' => $exception, 'message_id' => 123, 'message_properties' => [], 'message_body' => 'my_fake_body', 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->processor->process($this->message, []);
    }

    public function testSupportDataWillThrowException(): void
    {
        $exception = new \Exception('my_fake_exception_message');
        $this->consumerMock
            ->getData($this->message, [])
            ->willReturn('my_fake_get_data')
            ->shouldBeCalled();

        $this->consumerMock
            ->supportData('my_fake_get_data', $this->message, [])
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->error('Exception during consumer supportData.', ['exception' => $exception, 'message_id' => 123, 'message_properties' => [], 'message_body' => 'my_fake_body', 'data' => 'my_fake_get_data', 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('my_fake_exception_message');

        $this->processor->process($this->message, []);
    }

    public function testNotSupportData(): void
    {
        $this->consumerMock
            ->getData($this->message, [])
            ->willReturn('my_fake_get_data')
            ->shouldBeCalled();

        $this->consumerMock
            ->supportData('my_fake_get_data', $this->message, [])
            ->willReturn(false)
            ->shouldBeCalled();

        $this->loggerMock
            ->info('Consumer not support message.', ['message_id' => 123, 'message_properties' => [], 'message_body' => 'my_fake_body', 'data' => 'my_fake_get_data', 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->processor->process($this->message, []);
    }

    public function testValidateWillThrowUnexpectedTypeException(): void
    {
        $exception = new UnexpectedTypeException('my_fake_value', 'my_fake_expected');
        $this->consumerMock
            ->getData($this->message, [])
            ->willReturn('my_fake_get_data')
            ->shouldBeCalled();

        $this->consumerMock
            ->supportData('my_fake_get_data', $this->message, [])
            ->willReturn(true)
            ->shouldBeCalled();

        $this->consumerMock
            ->getConstraints('my_fake_get_data', $this->message, [])
            ->willReturn(['my_fake_get_constraints'])
            ->shouldBeCalled();

        $this->validatorMock
            ->validate('my_fake_get_data', ['my_fake_get_constraints'])
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->info('Consumer support message.', ['message_id' => 123, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();
        $this->loggerMock
            ->error('UnexpectedTypeException during data validation.', ['exception' => $exception, 'message_id' => 123, 'data' => 'my_fake_get_data', 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->processor->process($this->message, []);
    }

    public function testValidateWillThrowException(): void
    {
        $exception = new \Exception('my_fake_exception_message');
        $this->consumerMock
            ->getData($this->message, [])
            ->willReturn('my_fake_get_data')
            ->shouldBeCalled();

        $this->consumerMock
            ->supportData('my_fake_get_data', $this->message, [])
            ->willReturn(true)
            ->shouldBeCalled();

        $this->consumerMock
            ->getConstraints('my_fake_get_data', $this->message, [])
            ->willReturn(['my_fake_get_constraints'])
            ->shouldBeCalled();

        $this->validatorMock
            ->validate('my_fake_get_data', ['my_fake_get_constraints'])
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->info('Consumer support message.', ['message_id' => 123, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();
        $this->loggerMock
            ->error('Exception during data validation.', ['exception' => $exception, 'message_id' => 123, 'data' => 'my_fake_get_data', 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('my_fake_exception_message');

        $this->processor->process($this->message, []);
    }

    public function testValidateFail(): void
    {
        $this->consumerMock
            ->getData($this->message, [])
            ->willReturn('my_fake_get_data')
            ->shouldBeCalled();

        $this->consumerMock
            ->supportData('my_fake_get_data', $this->message, [])
            ->willReturn(true)
            ->shouldBeCalled();

        $this->consumerMock
            ->getConstraints('my_fake_get_data', $this->message, [])
            ->willReturn(['my_fake_get_constraints'])
            ->shouldBeCalled();

        $this->validatorMock
            ->validate('my_fake_get_data', ['my_fake_get_constraints'])
            ->willReturn(['my_fake_validate_value'])
            ->shouldBeCalled();

        $this->loggerMock
            ->info('Consumer support message.', ['message_id' => 123, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();
        $this->loggerMock
            ->warning('Invalid data for consumer.', ['violations' => ['my_fake_validate_value'], 'message_id' => 123, 'data' => 'my_fake_get_data', 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->processor->process($this->message, []);
    }

    public function testConsumeDataWillThrowException(): void
    {
        $exception = new \Exception('my_fake_exception_message');
        $this->consumerMock
            ->getData($this->message, [])
            ->willReturn('my_fake_get_data')
            ->shouldBeCalled();

        $this->consumerMock
            ->supportData('my_fake_get_data', $this->message, [])
            ->willReturn(true)
            ->shouldBeCalled();

        $this->consumerMock
            ->getConstraints('my_fake_get_data', $this->message, [])
            ->willReturn(['my_fake_get_constraints'])
            ->shouldBeCalled();

        $this->validatorMock
            ->validate('my_fake_get_data', ['my_fake_get_constraints'])
            ->willReturn([])
            ->shouldBeCalled();

        $this->consumerMock
            ->consumeData('my_fake_get_data', $this->message, [])
            ->willThrow($exception)
            ->shouldBeCalled();

        $this->loggerMock
            ->info('Consumer support message.', ['message_id' => 123, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();
        $this->loggerMock
            ->info('Valid data for consumer.', ['message_id' => 123, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();
        $this->loggerMock
            ->error('Exception during consume data.', ['exception' => $exception, 'message_id' => 123, 'data' => 'my_fake_get_data', 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('my_fake_exception_message');

        $this->processor->process($this->message, []);
    }

    public function testConsumeData(): void
    {
        $this->consumerMock
            ->getData($this->message, [])
            ->willReturn('my_fake_get_data')
            ->shouldBeCalled();

        $this->consumerMock
            ->supportData('my_fake_get_data', $this->message, [])
            ->willReturn(true)
            ->shouldBeCalled();

        $this->consumerMock
            ->getConstraints('my_fake_get_data', $this->message, [])
            ->willReturn(['my_fake_get_constraints'])
            ->shouldBeCalled();

        $this->validatorMock
            ->validate('my_fake_get_data', ['my_fake_get_constraints'])
            ->willReturn([])
            ->shouldBeCalled();

        $this->consumerMock
            ->consumeData('my_fake_get_data', $this->message, [])
            ->willReturn('my_fake_consume_data')
            ->shouldBeCalled();

        $this->loggerMock
            ->info('Consumer support message.', ['message_id' => 123, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();
        $this->loggerMock
            ->info('Valid data for consumer.', ['message_id' => 123, 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();
        $this->loggerMock
            ->info('Message consumed.', ['message_id' => 123, 'message_properties' => [], 'message_body' => 'my_fake_body', 'data' => 'my_fake_get_data', 'consumer' => $this->consumerClass, 'swarrot_processor' => 'consumer_processor'])
            ->shouldBeCalled();

        $this->assertEquals('my_fake_consume_data', $this->processor->process($this->message, []));
    }
}
