<?php

namespace App\Extensions;

use PhpAmqpLib\Connection\AMQPStreamConnection;

trait AmqpConnectionChannel
{
    public function setup () {
        $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASSWORD'), env('RABBITMQ_VHOST'));
        $channel = $connection->channel();

        return [ $connection, $channel ];
    }
}
