<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Tests\Broker\Consumer;

use Ensembl260\SwarrotExtensionBundle\Broker\Consumer\ConsumerJsonDataTrait;
use Ensembl260\SwarrotExtensionBundle\Broker\Exception\InvalidDataException;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;

class ConsumerJsonDataTraitTest extends TestCase
{
    private ConsumerJsonData $consumerJsonData;

    public function setUp(): void
    {
        $this->consumerJsonData = new ConsumerJsonData();
    }

    public function bodyProvider(): array
    {
        return [
            ['null', ''],
            ['true', true],
            ['false', false],
            ['1.1',  1.1],
            ['"fake_data"', 'fake_data'],
            ['["fake_data"]', ['fake_data']],
            ['{"fake_key":"fake_data"}', ['fake_key' => 'fake_data']],
            ['{"fake_key":["fake_data"]}', ['fake_key' => ['fake_data']]],
        ];
    }

    /**
     * @dataProvider bodyProvider
     */
    public function testGetData($body, $expectedData): void
    {
        self::assertEquals($expectedData, $this->consumerJsonData->getData(new Message($body), []));
    }

    public function invalidBodyProvider(): array
    {
        return [
            [
                str_repeat('[', 512).'"text"'.str_repeat('[', 512),
                'JSON error: "Maximum stack depth exceeded". Valid json expected.',
            ],
            [
                '{"":1]}',
                'JSON error: "State mismatch (invalid or malformed JSON)". Valid json expected.',
            ],
            [
                '"',
                'JSON error: "Control character error, possibly incorrectly encoded". Valid json expected.',
            ],
            [
                ']',
                'JSON error: "Syntax error". Valid json expected.',
            ],
            [
                "\xB1\x31",
                'JSON error: "Malformed UTF-8 characters, possibly incorrectly encoded". Valid json expected.',
            ],
            [
                '',
                'JSON error: "Syntax error". Valid json expected.',
            ],
            [
                null,
                'JSON error: "Syntax error". Valid json expected.',
            ],
        ];
    }

    /**
     * @dataProvider invalidBodyProvider
     */
    public function testGetDataWillThrowInvalidDataException($body, string $expectedErrorMessage): void
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        $this->consumerJsonData->getData(new Message($body), []);
    }
}

/**
 * phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
 * phpcs:disable Squiz.Classes.ClassFileName.NoMatch.
 */
class ConsumerJsonData
{
    use ConsumerJsonDataTrait;
}
