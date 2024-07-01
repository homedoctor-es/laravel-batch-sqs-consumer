<?php

namespace HomedoctorEs\BatchSqs;

use Aws\EventBridge\EventBridgeClient;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Arr;
use HomedoctorEs\BatchSqs\Sub\Queue\Connectors\SqsBatchConnector;

class SqsBatchServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/batch.sqs.php' => config_path('batch-sqs.php'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        parent::register();

        $this->registerSqsBatchQueueConnector();

    }


    /**
     * Register the SQS SNS connector for the Queue components.
     *
     * @return void
     */
    protected function registerSqsBatchQueueConnector()
    {
        $this->app->resolving('queue', function (QueueManager $manager) {
            $manager->extend('sqs-batch', function () {
                return new SqsBatchConnector;
            });
        });
    }


    /**
     * Parse and prepare the AWS credentials needed by the AWS SDK library from the config.
     *
     * @param  array  $config
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
