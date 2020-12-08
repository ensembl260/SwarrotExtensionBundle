<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Consumer;

use Swarrot\Broker\Message;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ValidatorException;

interface ConstraintConsumerInterface
{
    /**
     * @param mixed[] $data
     * @param Message $message
     * @param mixed[] $options
     *
     * @return Constraint[]
     * @throws ValidatorException
     */
    public function getConstraints(array $data, Message $message, array $options): array;
}
