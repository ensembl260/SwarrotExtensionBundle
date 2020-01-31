<?php

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\PublishException;
use MR\SwarrotExtensionBundle\Broker\Exception\UnrecoverableConsumerException;
use MR\SwarrotExtensionBundle\Broker\Exception\UnrecoverableException;
use MR\SwarrotExtensionBundle\Broker\Processor\Event\XDeathEvent;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FlattenException;

class ErrorPublisher implements ErrorPublisherInterface
{
    private const MESSAGE_TYPE = 'error';

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var SerializerInterface
     */
    private $serializer;

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
        $metadata = [];

        switch (true) {
            case $object instanceof XDeathEvent:
                $message = $object->getMessage();

                return array_replace($metadata, [
                    'rabbit' => [
                        'xdeath-event-type' => $object->getType(),
                        'options' => $object->getOptions(),
                        'message' => [
                            'id' => $message->getId(),
                            'body' => $message->getBody(),
                            'properties' => $message->getProperties(),
                        ],
                    ],
                ]);
            case $object instanceof RequestException:
                $request = $object->getRequest();
                $response = $object->getResponse();

                return array_replace($metadata, [
                    'guzzle' => [
                        'request' => [
                            'method' => $request->getMethod(),
                            'headers' => $request->getHeaders(),
                            'uri' => (string) $request->getUri(),
                        ],
                        'response' => [
                            'reason' => $response->getReasonPhrase(),
                            'statusCode' => $response->getStatusCode(),
                            'headers' => $response->getHeaders(),
                            'body' => (string) $response->getBody(),
                        ],
                    ],
                ]);
            case $object instanceof UnrecoverableConsumerException:
                $message = $object->getBrokerMessage();

                return array_replace($metadata, [
                    'rabbit' => [
                        'want-kill-consumer' => $object->wantKillConsumer(),
                        'want-rethrow' => $object->wantRethrow(),
                        'message' => [
                            'id' => $message->getId(),
                            'body' => $message->getBody(),
                            'properties' => $message->getProperties(),
                        ],
                    ],
                ]);
        }

        return $metadata;
    }

    /**
     * @param string $messageType
     * @param mixed $data
     * @param array $messageProperties
     * @param array $overridenConfig
     *
     * @throws PublishException
     */
    private function publish(string $messageType, $data, array $messageProperties = [], array $overridenConfig = []): void
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
            $exception = new FatalThrowableError($exception);
        }

        return FlattenException::create($exception);
    }

    /**
     * @param object $object
     *
     * @return string
     */
    private function getRoutingKey($object): string
    {
        switch (true) {
            case $object instanceof XDeathEvent:
                return sprintf($this->routingKey, 'error.rabbit.xdeath');
            case $object instanceof UnrecoverableException:
                return sprintf($this->routingKey, 'error.unrecoverable_exception');
            case $object instanceof \Throwable:
                return sprintf($this->routingKey, 'error.exception');
            default:
                return sprintf($this->routingKey, 'error.unknown');
        }
    }
}
