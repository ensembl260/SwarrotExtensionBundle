<?php

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class ErrorPublisher implements ErrorPublisherInterface
{
    private const MESSAGE_TYPE = 'error';

    private PublisherInterface $publisher;
    private string $routingKey;
    private SerializerInterface $serializer;

    public function __construct(
        PublisherInterface $publisher,
        string $routingKey,
        SerializerInterface $serializer
    ) {
        $this->publisher = $publisher;
        $this->routingKey = $routingKey;
        $this->serializer = $serializer;
    }

    public function xdeathEvent(XDeathEvent $xDeathEvent): void
    {
        $this->publish(
            self::MESSAGE_TYPE,
            [
                'data' => $this->getData($xDeathEvent),
                'metadata' => $this->getMetadata($xDeathEvent),
            ],
            [],
            [
                'routing_key' => sprintf($this->routingKey, 'error.rabbit.xdeath'),
            ]
        );
    }

    public function exception(\Throwable $exception): void
    {
        $this->publish(
            self::MESSAGE_TYPE,
            [
                'data' => $this->getData($exception),
                'metadata' => $this->getMetadata($exception),
            ],
            [],
            [
                'routing_key' => $this->getRoutingKey($exception),
            ]
        );
    }

    /**
     * @param mixed $object
     *
     * @return array
     */
    protected function getData($object)
    {
        $data = [
            'reason' => 'An error occured.',
            'hostname' => gethostname(),
        ];

        switch (true) {
            case $object instanceof XDeathEvent:
                $flattenException = $this->flattenException($object->getException())->toArray();

                return array_replace($data, [
                    'reason' => 'Rabbit MQ Fail',
                    'exception' => reset($flattenException),
                ]);
            case $object instanceof \Throwable:
                $flattenException = $this->flattenException($object)->toArray();

                return array_replace($data, [
                    'reason' => $object->getMessage(),
                    'exception' => reset($flattenException),
                ]);
        }

        return $data;
    }

    /**
     * @param mixed $object
     *
     * @return array
     */
    protected function getMetadata($object)
    {
        if (!$object instanceof XDeathEvent) {
            return [];
        }

        $message = $object->getMessage();

        return [
            'rabbit' => [
                'xdeath-event-type' => $object->getType(),
                'options' => $object->getOptions(),
                'message' => [
                    'id' => $message->getId(),
                    'body' => $message->getBody(),
                    'properties' => $message->getProperties(),
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $data
     * @param mixed[] $messageProperties
     * @param mixed[] $overridenConfig
     *
     * @throws \MR\SwarrotExtensionBundle\Broker\Exception\PublishException
     */
    private function publish(string $messageType, array $data, array $messageProperties = [], array $overridenConfig = []): void
    {
        $this->publisher->publish(
            $messageType,
            $this->serializer->serialize($data, 'json'),
            array_merge_recursive($messageProperties, ['content_type' => 'application/json']),
            $overridenConfig
        );
    }

    private function flattenException(\Throwable $exception): FlattenException
    {
        if (!$exception instanceof \Exception) {
            $exception = new FatalError($exception->getMessage(), $exception->getCode(),error_get_last());
        }

        return FlattenException::create($exception);
    }

    private function getRoutingKey(object $object): string
    {
        switch (true) {
            case $object instanceof XDeathEvent:
                return sprintf($this->routingKey, 'error.rabbit.xdeath');
            case $object instanceof \Throwable:
                return sprintf($this->routingKey, 'error.exception');
            default:
                return sprintf($this->routingKey, 'error.unknown');
        }
    }
}
