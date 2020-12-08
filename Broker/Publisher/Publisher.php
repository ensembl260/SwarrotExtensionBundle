<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\PublishException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Swarrot\SwarrotBundle\Broker\Publisher as SwarrotPublisher;

class Publisher implements PublisherInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private SwarrotPublisher $publisher;
    private MessageFactoryInterface $messageFactory;

    public function __construct(
        SwarrotPublisher $publisher,
        MessageFactoryInterface $messageFactory
    ) {
        $this->publisher = $publisher;
        $this->messageFactory = $messageFactory;
        $this->logger = new NullLogger();
    }

    public function publish(string $messageType, $data, array $messageProperties = [], array $overridenConfig = []): void
    {
        $config = array_merge($this->publisher->getConfigForMessageType($messageType), $overridenConfig);
        try {
            $messageProperties['headers']['X-Routing-key'] = $config['routing_key'] ?: '';

            $this->publisher->publish(
                $messageType,
                $this->messageFactory->createMessage($data, $messageProperties),
                $config
            );

            $this->logger->info(
                'Publish success.', 
                [
                    'data' => $data, 
                    'message_type' => $messageType, 
                    'connection' => $config['connection'], 
                    'exchange' => $config['exchange'], 
                    'routing_key' => $config['routing_key'], 
                    'class' => __CLASS__, 
                    'line' => __LINE__
                ]
            );
        } catch (\Exception $exception) {
            $errorMessage = 'Publish fail.';
            $this->logger->error(
                $errorMessage, 
                [
                    'exception' => $exception, 
                    'data' => $data, 
                    'message_type' => $messageType, 
                    'connection' => $config['connection'], 
                    'exchange' => $config['exchange'], 
                    'routing_key' => $config['routing_key'], 
                    'class' => __CLASS__, 
                    'line' => __LINE__
                ]
            );
            throw new PublishException($data, $messageType, $errorMessage, 0, $exception);
        }
    }
}
