<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Exception;

class PublishException extends \Exception
{
    /**
     * @var string
     */
    private $object;

    /**
     * @var string
     */
    private $messageType;

    /**
     * @param string $object
     * @param string $messageType
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($object, $messageType, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->object = $object;
        $this->messageType = $messageType;
    }

    /**
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }
}
