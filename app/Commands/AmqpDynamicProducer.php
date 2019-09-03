<?php

namespace App\Commands;

use Anik\Amqp\Exchange;
use Anik\Amqp\PublishableMessage;
use Faker\Factory;
use LaravelZero\Framework\Commands\Command;

class AmqpDynamicProducer extends Command
{
    protected $signature = 'amqp:publisher:dynamic {--auto}';

    protected $description = 'RabbitMQ AMQP dynamic producer';

    public function handle () {
        $auto = $this->option('auto');

        if (!$auto) {
            $text = trim($this->ask('Write any message: '));
            $ex = trim($this->ask('Write an exchange name: '));
            $r = trim($this->ask('Write a routing key: '));
        }

        if (empty($text)) {
            $text = json_encode([
                'name'  => Factory::create()->name,
                'email' => Factory::create()->email,
            ]);
        }

        if (empty($ex)) {
            $ex = 'direct.exchange';
        }

        if (empty($r)) {
            $r = 'direct.routing.key';
        }

        app('amqp')->publish($text, $r, [
            'exchange' => [
                'declare' => true,
                'type'    => 'direct',
                'name'    => $ex,
            ],
        ]);
    }
}
