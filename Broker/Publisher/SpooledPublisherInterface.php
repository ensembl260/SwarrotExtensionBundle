<?php

declare(strict_types=1);

namespace MR\SwarrotExtensionBundle\Broker\Publisher;

use MR\SwarrotExtensionBundle\Broker\Exception\PublishException;

interface SpooledPublisherInterface extends PublisherInterface
{
    /**
     * Use to flush the spooled message
     *
     * @throws PublishException
     */
    public function flush(): void;
}
