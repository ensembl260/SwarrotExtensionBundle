<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Consumer;

use MR\SwarrotExtensionBundle\Broker\Consumer\ConsumerJsonDataTrait;
use MR\SwarrotExtensionBundle\Broker\Exception\InvalidDataException;
use Swarrot\Broker\Message;
use PHPUnit\Framework\TestCase;

class ConsumerJsonDataTraitTest extends TestCase
{
    /**
     * @var ConsumerJsonData
     */
    private $consumerJsonData;

    public function setUp(): void
    {
        $this->consumerJsonData = new ConsumerJsonData();
    }

    public function bodyProvider(): array
    {
        return [
            [
                'null',
                ''
            ],
            [
                'true',
                true
            ],
            [
                'false',
                false
            ],
            [
                1.1,
                1.1
            ],
            [
                '1.1',
                1.1
            ],
            [
                '"fake_data"',
                'fake_data'
            ],
            [
                '["fake_data"]',
                ['fake_data']
            ],
            [
                '{"fake_key":"fake_data"}',
                ['fake_key' => 'fake_data']
            ],
            [
                '{"fake_key":["fake_data"]}',
                ['fake_key' => ['fake_data']]
            ],
        ];
    }

    /**
     * @dataProvider bodyProvider
     *
     * @param mixed $body
     * @param mixed $expectedData
     */
    public function testGetData($body, $expectedData): void
    {
        $this->assertEquals($expectedData, $this->consumerJsonData->getData(new Message($body), []));
    }

    public function invalidBodyProvider(): array
    {
        return [
            [
                str_repeat('[', 512) . '"text"' . str_repeat('[', 512),
                'JSON error: "Maximum stack depth exceeded". Valid json expected.'
            ],
            [
                '{"":1]}',
                'JSON error: "State mismatch (invalid or malformed JSON)". Valid json expected.'
            ],
            [
                '"',
                'JSON error: "Control character error, possibly incorrectly encoded". Valid json expected.'
            ],
            [
                ']',
                'JSON error: "Syntax error". Valid json expected.'
            ],
            [
                "\xB1\x31",
                'JSON error: "Malformed UTF-8 characters, possibly incorrectly encoded". Valid json expected.'
            ],
            [
                '',
                'JSON error: "Syntax error". Valid json expected.'
            ],
            [
                null,
                'JSON error: "Syntax error". Valid json expected.'
            ],
        ];
    }

    /**
     * @dataProvider invalidBodyProvider
     *
     * @param mixed $body
     * @param string $expectedErrorMessage
     */
    public function testGetDataWillThrowInvalidDataException($body, string $expectedErrorMessage): void
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        $this->consumerJsonData->getData(new Message($body), []);
    }
}

class ConsumerJsonData
{
    use ConsumerJsonDataTrait;
}
