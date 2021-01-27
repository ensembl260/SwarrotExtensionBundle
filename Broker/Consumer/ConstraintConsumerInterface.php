<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Consumer;

use Swarrot\Broker\Message;

interface ConstraintConsumerInterface
{
    /**
     * @param mixed $data
     * @param mixed[] $options
     *
     * @return \Symfony\Component\Validator\Constraint[]
     *
     * @throws \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function getConstraints($data, Message $message, array $options): array;
}
