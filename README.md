# SwarrotExtensionBundle

## Consumer

With this bundle you need only to create and register the consumer.

First of all, create your consumer class.

```php
<?php

declare (strict_types=1);

namespace AppBundle\Broker\Consumer;

use Ensembl260\SwarrotExtensionBundle\Broker\Consumer\ConsumerInterface;
use Swarrot\Broker\Message;

class MyConsumer implements ConsumerInterface
{
    public function getData(Message $message, array $options)
    {
        /**
         * Your own data recuperation here
         * 
         * the return data gonna be passed in the consumeData 
         */
        return ['my_data'];
    }
    
    public function consumeData($data, Message $message, array $options)
    {
        /**
         * your own logic.
         * 
         * return true :
         *   - when your process end with success
         *   - when your process end with error but you dont want to kill consumer / NACK 
         * return false :
         *   - when your process end with critical error and you want to stop the current consumer
         * throw / let throw an exception :
         *   - without the AckProcessor it will stop the consumer
         *   - with the AckProcessor it will NACK the current message (and apply queue dlx / dlk if they are configured)
         */  
        return true;
    }
}
```

If your payload is json_encode you can just :

```php
/**
 * This traits is used to json_decode the message body
 * You can implement your own getData method
 */
use ConsumerJsonDataTrait;
```

If you want to add a validation pass before consumeData you can implement `ConstraintConsumerInterface`.

```php
public function getConstraints($data, Message $message, array $options)
{
    /**
     * Return a Constraint to validate the $data (come from the getData method)
     */
    return [
        new Assert\NotNull(['message' => 'Message body should not be null.']),
        new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'data' => new Assert\Collection([
                    'allowExtraFields' => true,
                    'fields' => [
                        'id' => [
                            new Assert\NotBlank(),
                            new Assert\Type('int'),
                        ],
                    ],
                ]),
            ],
        ]),
    ];
}
```

If you want to skip some message you can implement `SupportConsumerInterface`.

```php
public function supportData($data, Message $message, array $options) 
{
    /**
     * your own support logic. 
     * 
     * you must return true/false if you want to keep/skip current message
     */
    return true;
}
```

Declare this class has service and add `broker.processor` tag with id attribute. 

```yaml
services:
    AppBundle\Broker\Consumer\MyConsumer:
        tags:
            - { name: 'broker.processor', id: 'my_consumer.processor' }

```

Use your tag name in the swarrot configuration.

```yaml
swarrot:
    consumers:
        my_consumer:
            processor: 'my_consumer.processor'
            ...
```

## Create mapping

Create one file per vhost. In this file configure the exchanges and queues configuration.

```yaml
'your_vhost_name':
  parameters:
    with_dl: false # If true, all queues will have a dl and the corresponding mapping with the exchange "dl"
    with_unroutable: false # If true, all exchange will be declared with an unroutable config

  exchanges:
    your_exchange_name:
      type: 'topic' # direct, fanout, topic or headers
      durable: true # true or false

  queues:
    your_queue_name:
      durable: true # true or false
      arguments:
        x-dead-letter-exchange: 'internal_waiting_5' # internal_waiting_5 internal_waiting_10 internal_waiting_30 internal_waiting_60 internal_waiting_600
        x-dead-letter-routing-key: 'your_queue_name.dl'
      bindings:
        - exchange: 'your_exchange_name'
          routing_key: 'your_routing_key_name'
```

For apply configuration run this commande `bin/console swarrot_extension:vhost:mapping:create -r config/rabbit-vhost.yaml`.
If you use the option `-r` or `--retry-queues` you load default retry queues configuration

The default retry queues configuration:
- internal_waiting_5: retry message after 5 seconds
- internal_waiting_10: retry message after 10 seconds
- internal_waiting_30: retry message after 30 seconds
- internal_waiting_60: retry message after 60 seconds
- internal_waiting_600: retry message after 600 seconds

## Middlewares

If you want to limit the maximum retry of a message you can use the `XDeathMaxCountProcessor`. 
> This processor dispatch `XDeathEvent::MAX_COUNT_REACHED` when the Message Xdeath count is reached.

If you want to limit the maximum lifetime of a message (during retry process) you can use the `XDeathMaxLifetimeProcessor`
> This processor dispatch `XDeathEvent::MAX_LIFETIME_REACHED` when the Message Xdeath lifetime is reached.

```yaml
swarrot:
    consumers:
        my_consumer:
            processor: 'my_consumer.processor'
            middleware_stack: 
                - configurator: 'swarrot_extension.processor.x_death_max_count'
                  extras:
                     x_death_max_count: 10 # (default: 300)
                - configurator: 'swarrot_extension.processor.x_death_max_lifetime'
                  extras:
                     x_death_max_lifetime: 100 # (default: 3600 seconds)
```

## Custom Message Factory

If you need to change the `Message` construction you can implement your own `MessageFactoryInterface`.

Update the config with :

```yaml
swarrot_extension:
    message_factory: 'Ensembl260\Infrastructure\Broker\Publisher\MessageFactory'
```

## Error Publisher

When using middlewares such as `XDeathMaxCountProcessor`, etc... the ErrorPublisher publish a message reporting the encoutered error.
To be able to publish, an `error` message type must be configured in your swarrot config:

```yaml
swarrot:
  message_types:
    error:
      connection: 'my_app'
      exchange: 'my_app'
      routing_key: 'my_app.error'
```

By default, the `ErrorPublisher` will override the routing key with the error type, e.g. `error.rabbit.xdeath`.
You can override the routing key pattern in the bundle configuration:

```yaml
swarrot_extension:
  error_publisher:
    routing_key_pattern: 'my_app.%s'  # Override the default error routing key pattern (optional)

```

You can also use your own error publisher service:

```yaml
swarrot_extension:
  error_publisher:
    service: 'my_app.custom_error_publisher'  # Override the default error publisher (optional)
```

## Tests

If you use `ConstrainedConsumerTestCaseTrait` and `SupportConsumerTestCaseTrait` in your test you just need to override `validDataProvider` / `invalidDataProvider` / `notSupportDataProvider` and `supportDataProvider`.
