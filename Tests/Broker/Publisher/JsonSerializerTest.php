<?php

declare(strict_types=1);

namespace Ensembl260\SwarrotExtensionBundle\Tests\Broker\Publisher;

use Ensembl260\SwarrotExtensionBundle\Broker\Publisher\JsonSerializer;
use PHPUnit\Framework\TestCase;

class JsonSerializerTest extends TestCase
{
    private JsonSerializer $jsonSerializer;

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
     * @param mixed|string|string[] $data
     * @param mixed|string|string[] $expectedData
     */
    public function testSerialize($data, $expectedData): void
    {
        self::assertEquals($expectedData, $this->jsonSerializer->serialize($data, 'json'));
    }

    public function testSerializeWillThrowInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This serializer only support "json".');

        $this->jsonSerializer->serialize('data', 'not_json');
    }
}
