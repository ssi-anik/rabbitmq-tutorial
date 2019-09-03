<?php

namespace App\Commands;

use Anik\Amqp\ConsumableMessage;
use LaravelZero\Framework\Commands\Command;

class AmqpClosureConsumerHeaders extends Command
{
    protected $signature = 'amqp:consumer:closure:headers {--auto}';

    protected $description = 'RabbitMQ AMQP closure consumer w/ headers';

    public function handle () {
        if (!$this->option('auto')) {
            $ex = trim($this->ask('Write an exchange name: '));
            $qn = trim($this->ask('Write a queue name: '));
            $bk = trim($this->ask('Write a binding key: '));
        }

        if (empty($ex)) {
            $ex = 'direct.exchange';
        }

        if (empty($bk)) {
            $bk = 'direct.routing.key';
        }

        if (empty($qn)) {
            $qn = 'direct.exchange.queue';
        }

        app('amqp')->consume(function (ConsumableMessage $message) {
            $applicationHeaders = $message->getAmqpMessage()->get_properties()['application_headers'] ?? null;
            echo json_encode($applicationHeaders ? $applicationHeaders->getNativeData() : []) . PHP_EOL;

            $message->getDeliveryInfo()->acknowledge();
        }, $bk, [
            'connection' => 'another-rabbitmq-broker',
            'exchange'   => [
                'declare' => true,
                'type'    => 'direct',
                'name'    => $ex,
            ],
            'queue'      => [
                'name'      => $qn,
                'declare'   => true,
                'exclusive' => false,
            ],
            'qos'        => [
                'enabled'            => true,
                'qos_prefetch_count' => 5,
            ],
        ]);
    }
}
