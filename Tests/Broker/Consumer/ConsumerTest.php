<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Consumer;

use MR\SwarrotExtensionBundle\Broker\Consumer\ConstraintConsumerInterface;
use MR\SwarrotExtensionBundle\Broker\Consumer\ConsumerInterface;
use MR\SwarrotExtensionBundle\Broker\Consumer\SupportConsumerInterface;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Symfony\Component\Validator\Constraints as Assert;

class ConsumerTest extends TestCase
{
    use ConstrainedConsumerTestCaseTrait;
    use SupportConsumerTestCaseTrait;

    public function setUp(): void
    {
        $this->consumer = new Consumer();
    }

    public function validDataProvider(): array
    {
        return [
            [
                [
                    'consumed' => true,
                    'supported' => true,
                ],
            ],
            [
                [
                    'consumed' => false,
                    'supported' => false,
                ],
            ],
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            [
                [],
                [
                    '[consumed]' => 'This field is missing.',
                    '[supported]' => 'This field is missing.',
                ],
            ],
            [
                [
                    'consumed' => 'true',
                    'supported' => 'true',
                ],
                [
                    '[consumed]' => 'This value should be of type bool.',
                    '[supported]' => 'This value should be of type bool.',
                ],
            ],
        ];
    }

    public function notSupportDataProvider(): array
    {
        return [
            [
                [
                    'supported' => false,
                ],
            ],
        ];
    }

    public function supportDataProvider(): array
    {
        return [
            [
                [
                    'supported' => true,
                ],
            ],
        ];
    }

    public function getConsumer()
    {
        return $this->consumer;
    }
}

class Consumer implements ConsumerInterface, ConstraintConsumerInterface, SupportConsumerInterface
{
    public function getConstraints($data, Message $message, array $options): array
    {
        return [
            new Assert\NotNull(['message' => 'Message body should not be null.']),
            new Assert\Collection([
                'allowExtraFields' => true,
                'fields' => [
                    'consumed' => [new Assert\Type('bool')],
                    'supported' => [new Assert\Type('bool')],
                ],
            ]),
        ];
    }

    public function consumeData(array $data, Message $message, array $options): bool
    {
        return $data['consumed'] === true;
    }

    public function supportData($data, Message $message, array $options): bool
    {
        return $data['supported'] === true;
    }

    public function getData(Message $message, array $options): array
    {
        return [];
    }
}
