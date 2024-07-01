<?php

namespace HomedoctorEs\EventBridgeSqs\Sub\Queue\Jobs;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class EventBridgeEventDispatcherJob extends SqsJob implements JobContract
{
    /**
     * @inheritDoc
     */
    public function fire()
    {
        if (!$this->isValid()) {
            if ($this->container->bound('log')) {
                Log::error('SqsSnsQueue: Invalid payload. '.
                    'Make sure your JSON is a valid JSON object and has fields detail and detail-type.', $this->job);
            }

            $this->release();

            return;
        }
        
        if ($eventName = $this->resolveName()) {
            $this->resolve(Dispatcher::class)->dispatch($eventName, [
                'payload' => $this->message(),
                'subject' => $this->subject(),
            ]);
        }

        $this->delete();
    }

    /**
     * @inheritDoc
     */
    protected function failed($e)
    {
        dump($e->getMessage());
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->subject();
    }

    /**
     * @inheritDoc
     */
    public function resolveName()
    {
        return $this->getName();
    }

    /**
     * Verifies that the SNS message sent to the queue can be processed.
     *
     * @return bool
     */
    private function isValid()
    {
        $payload = $this->payload();
        return Arr::exists($payload, 'detail') && Arr::exists($payload, 'detail-type');
    }

    /**
     * Get the job SNS subject.
     *
     * @return string
     */
    public function subject()
    {
        return $this->payload()['detail-type'] ?? '';
    }

    /**
     * Get the job SNS message.
     *
     * @return string
     */
    public function message()
    {
        $detail = $this->payload()['detail'] ?? '[]';
        if (is_array($detail)) {
            return $detail;
        }

        return json_decode($detail, true);
    }

}
