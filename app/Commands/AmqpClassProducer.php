<?php

namespace App\Commands;

use Anik\Amqp\Exchange;
use Anik\Amqp\PublishableMessage;
use Faker\Factory;
use LaravelZero\Framework\Commands\Command;

class AmqpClassProducer extends Command
{
    protected $signature = 'amqp:publisher:class {--auto}';

    protected $description = 'RabbitMQ AMQP class producer';

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

        $msg = new PublishableMessage($text);
        $msg->setExchange(new Exchange($ex, [
            'type'    => 'direct',
            'declare' => true,
        ]));

        app('amqp')->publish($msg, $r);
    }
}
