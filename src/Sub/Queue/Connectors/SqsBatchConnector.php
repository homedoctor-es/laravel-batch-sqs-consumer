<?php

namespace HomedoctorEs\BatchSqs\Sub\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;
use HomedoctorEs\BatchSqs\SqsBatchServiceProvider;
use HomedoctorEs\BatchSqs\Sub\Queue\SqsBatchQueue;

class SqsBatchConnector extends SqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        return new SqsBatchQueue(
            new SqsClient(SqsBatchServiceProvider::prepareConfigurationCredentials($config)),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            Arr::get($config, 'suffix', ''),
        );
    }
}
