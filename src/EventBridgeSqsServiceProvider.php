<?php

namespace HomedoctorEs\EventBridgeSqs;

use Illuminate\Contracts\Broadcasting\Factory as BroadcastManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Arr;
use HomedoctorEs\EventBridgeSqs\Sub\Queue\Connectors\EventBridgeSqsConnector;

class EventBridgeSqsServiceProvider extends ServiceProvider
{

    /**
     * @inheritDoc
     */
    public function register()
    {
        parent::register();

        $this->registerEventBridgeSqsQueueConnector();
    }


    /**
     * Register the SQS EventBridge connector for the Queue components.
     *
     * @return void
     */
    protected function registerEventBridgeSqsQueueConnector()
    {
        $this->app->resolving('queue', function (QueueManager $manager) {
            $manager->extend('eventbridge-sqs', function () {
                return new EventBridgeSqsConnector;
            });
        });
    }


    /**
     * Parse and prepare the AWS credentials needed by the AWS SDK library from the config.
     *
     * @param array $config
     * @return array
     */
    public static function prepareConfigurationCredentials(array $config): array
    {
        if (static::configHasCredentials($config)) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }

    /**
     * Make sure some AWS credentials were provided to the configuration array.
     *
     * @return bool
     */
    private static function configHasCredentials(array $config): bool
    {
        return Arr::has($config, ['key', 'secret'])
            && Arr::get($config, 'key')
            && Arr::get($config, 'secret');
    }

}
