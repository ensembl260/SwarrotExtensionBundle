<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\PublishException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Swarrot\SwarrotBundle\Broker\Publisher as SwarrotPublisher;

class SpooledPublisher implements SpooledPublisherInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $waitingCalls;

    /**
     * @var SwarrotPublisher
     */
    private $publisher;

    /**
     * @var MessageFactoryInterface
     */
    private $messageFactory;

    public function __construct(
        SwarrotPublisher $publisher,
        MessageFactoryInterface $messageFactory
    ) {
        $this->publisher = $publisher;
        $this->messageFactory = $messageFactory;
        $this->waitingCalls = [];
        $this->logger = new NullLogger();
    }

    /**
     * @param string $messageType
     * @param mixed $data
     * @param array $messageProperties
     * @param array $overridenConfig
     *
     * @throws PublishException
     */
    public function publish(string $messageType, $data, array $messageProperties = [], array $overridenConfig = []): void
    {
        $config = array_merge($this->publisher->getConfigForMessageType($messageType), $overridenConfig);
        $messageProperties['headers']['X-Routing-key'] = $config['routing_key'] ?: '';

        $this->waitingCalls[] = [
            $messageType,
            $this->messageFactory->createMessage($data, $messageProperties),
            $config,
        ];

        $this->logger->debug('Message spooled.', ['data' => $data, 'message_type' => $messageType, 'connection' => $config['connection'], 'exchange' => $config['exchange'], 'routing_key' => $config['routing_key'], 'class' => __CLASS__, 'line' => __LINE__]);
    }

    public function flush(): void
    {
        try {
            for ($i = 0; $i < count($this->waitingCalls); $i++) {
                call_user_func_array([$this->publisher, 'publish'], $this->waitingCalls[$i]);
                $this->logger->info('Spooled message published successfully.', ['data' => $this->waitingCalls[$i][1], 'message_type' => $this->waitingCalls[$i][0], 'connection' => $this->waitingCalls[$i][2]['connection'], 'exchange' => $this->waitingCalls[$i][2]['exchange'], 'routing_key' => $this->waitingCalls[$i][2]['routing_key'], 'class' => __CLASS__, 'line' => __LINE__]);
            }

            $this->clear();
        } catch (\Exception $exception) {
            $errorMessage = 'Spooled message publish fail.';
            $this->logger->error($errorMessage, ['exception' => $exception, 'data' => $this->waitingCalls[$i][1], 'message_type' => $this->waitingCalls[$i][0], 'connection' => $this->waitingCalls[$i][2]['connection'], 'exchange' => $this->waitingCalls[$i][2]['exchange'], 'routing_key' => $this->waitingCalls[$i][2]['routing_key'], 'class' => __CLASS__, 'line' => __LINE__]);
            for ($j = $i+1; $j < count($this->waitingCalls); $j++) {
                $this->logger->debug('Spooled message not flushed.', ['data' => $this->waitingCalls[$j][1], 'message_type' => $this->waitingCalls[$j][0], 'connection' => $this->waitingCalls[$j][2]['connection'], 'exchange' => $this->waitingCalls[$j][2]['exchange'], 'routing_key' => $this->waitingCalls[$j][2]['routing_key'], 'class' => __CLASS__, 'line' => __LINE__]);
            }
            throw new PublishException($this->waitingCalls[$i][1], $this->waitingCalls[$i][0], $errorMessage, 0, $exception);
        }
    }

    public function clear()
    {
        $this->waitingCalls = [];
    }
}
