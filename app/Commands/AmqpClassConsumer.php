<?php

namespace App\Commands;

use Anik\Amqp\Exchange;
use App\Extensions\CustomConsumableMessage;
use LaravelZero\Framework\Commands\Command;

class AmqpClassConsumer extends Command
{
    protected $signature = 'amqp:consumer:class {--auto}';

    protected $description = 'RabbitMQ AMQP class consumer';

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

        $consumable = new CustomConsumableMessage();
        $consumable->setExchange(new Exchange($ex, [
            'declare' => true,
            'type'    => 'direct',
        ]));

        // exchange is set using class,
        // queue is set on the fly.
        app('amqp')->consume($consumable, $bk, [
            // another connection name <=> config('amqp.connections.another-rabbitmq-broker');
            'connection' => 'another-rabbitmq-broker',
            'channel_id' => 1010,
            'queue'      => [
                'name'      => $qn,
                'declare'   => true,
                'exclusive' => false,
            ],
            'qos'        => [
                'enabled'            => true,
                'qos_prefetch_count' => 5,
            ],
            /*'consumer' => [
                'no_ack' => true,
            ],*/
        ]);
    }
}
