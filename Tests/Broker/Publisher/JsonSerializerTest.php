<?php
declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Tests\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Publisher\JsonSerializer;
use PHPUnit\Framework\TestCase;

class JsonSerializerTest extends TestCase
{
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    public function setUp(): void
    {
        $this->jsonSerializer = new JsonSerializer();
    }

    public function serializeDataProvider(): array
    {
        return [
            ['foo', '"foo"'],
            [
                ['foo' => 'foo'],
                '{"foo":"foo"}',
            ],
        ];
    }

    /**
     * @dataProvider serializeDataProvider
     *
     * @param $data
     * @param $expectedData
     */
    public function testSerialize($data, $expectedData): void
    {
        $this->assertEquals($expectedData, $this->jsonSerializer->serialize($data, 'json'));
    }

    public function testSerializeWillThrowInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This serializer only support "json".');

        $this->jsonSerializer->serialize('data', 'not_json');
    }
}
