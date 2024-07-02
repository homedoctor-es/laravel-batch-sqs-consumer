# AWS EventBridge SQS consumer for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/homedoctor-es/laravel-eventbridge-sqs-consumer.svg?style=flat-square)](https://packagist.org/packages/homedoctor-es/laravel-eventbridge-sqs-consumer)
[![Software License](https://img.shields.io/badge/license-GNU-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/homedoctor-es/laravel-eventbridge-sqs-consumer.svg?style=flat-square)](https://packagist.org/packages/homedoctor-es/laravel-eventbridge-sqs-consumer)


We simply have to listen to these messages pushed to an SQS queue and act upon them. The only difference here is that we don't use the default Laravel SQS driver as the messages pushed are not following Laravel's classic JSON payload for queued Jobs/Events pushed from a Laravel application. The messages from EventBridge and SNS are simpler.

## Prerequisites

1. This package installed and configured
2. At least one SQS Queue - **one queue per Laravel application subscribing**
3. At least one Event Bridge Eveng Bus
4. An [SQS subscription](./docs/sqs-subscription.jpg) between your EventBus and your SQS Queue.
5. The relevant [Access policies configured](https://docs.aws.amazon.com/sns/latest/dg/sns-access-policy-use-cases.html), especially if you want to be able to publish messages directly from the AWS Console.

## Installation

You can install the package on a Laravel 8+ application via composer:

```bash
composer require homedoctor-es/laravel-eventbridge-sqs-consumer
```

Then, add `HomedoctorEs\EventBridgeSqs\EventBridgeSqsServiceProvider::class` to load the driver in `config/app.php` file automatically.

### Configuration

Make sure to define your [environment variables](https://laravel.com/docs/configuration#environment-configuration) accordingly:

```dotenv
# both drivers require:
AWS_DEFAULT_REGION=you-region
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
```

Once the package is installed and similar to what you would do for a standard Laravel SQS queue, you will need to add the following connection and configure your credentials in the `config/queue.php` configuration file:

```php
'connections' => [
    // ...
    'eventbridge-sqs' => [
        'driver' => 'eventbridge-sqs',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'endpoint' => env('AWS_URL'),
        'prefix' => env('SQS_SNS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
        'queue' => env('SQS_SNS_QUEUE', 'app1_queue'),
        'suffix' => env('SQS_SNS_SUFFIX'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    // ...
],
```

Once your queue is configured properly, you will need to be able to define which listeners you would like to use for which kind of incoming events. In order to do so, you'll need to create Laravel listeners and associate the events through a Service Provider the package can create for you.

### Registering Events & Listeners

You'll need a Service Provider in order to define the mapping for each Subscribed event and its Listeners. You can use a separated one or you can add the $listen property in any provider you have.

The `listen` property contains an array of all events (keys) and their listeners (values). Unlike the standard Laravel `EventServiceProvider`, you can only define one Listeners per event, however you may add as many events to this array as your application requires.

#### Using the Broadcast Name

You can subscribe to an event by using its Broadcast name, for example, if you brroadas an event called orders.shipped:

```php
use App\Listeners\PubSub\SendShipmentNotification;

/**
 * The event handler mappings for subscribing to PubSub events.
 *
 * @var array
 */
protected $listen = [
    'orders.shipped' => [
        SendShipmentNotification::class,
    ],
];
```

You may do whatever you want from that generic `OrdersListener`, you could even [dispatch more events](https://laravel.com/docs/events) internally within your application.

### Defining Listeners

Here we are simply re-using standard Laravel event listeners. The only difference being the function definition of the main `handle()` method which differs slightly. Instead of expecting an instance of an Event class passed, we simply receive the `payload` and the `subject`, if it's found.

```php
/**
 * Handle the event.
 *
 * @return void
 */
public function handle(array $payload, string $subject = '')
{
    // ...
}
```

Feel free to queue these listeners, just like you would with a standard Laravel Listeners.


**Note:** you will still need to make sure the mapping within your desired provider is configured.

## Credits

- [laravel-aws-pubsub](https://github.com/Pod-Point/laravel-aws-pubsub) for some inspiration

## License

The GNU License (GNU). Please see [License File](LICENSE.md) for more information.

---

