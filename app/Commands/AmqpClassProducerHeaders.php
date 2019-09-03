<?php

namespace App\Commands;

use Anik\Amqp\Exchange;
use Anik\Amqp\PublishableMessage;
use Faker\Factory;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Wire\AMQPTable;

class AmqpClassProducerHeaders extends Command
{
    protected $signature = 'amqp:publisher:class:headers {--auto}';

    protected $description = 'RabbitMQ AMQP class producer w/ headers';

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

        app('amqp')->publish($msg, $r, [
            'message' => [
                'content_type'        => 'application/json',
                'application_headers' => new AMQPTable([
                    'header-slug'        => Factory::create()->slug,
                    'header-mac-address' => Factory::create()->macAddress,
                    'header-phone'       => Factory::create()->phoneNumber,
                    'header-user-agent'  => Factory::create()->userAgent,
                ]),
            ],
        ]);
    }
}
