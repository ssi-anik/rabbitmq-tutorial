<?php

namespace App\Commands;

use App\Extensions\AmqpConnectionChannel;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DefaultMqReceiver extends Command
{
    use AmqpConnectionChannel;

    protected $signature = 'default:receiver';

    protected $description = 'RabbitMQ default receiver';

    public function handle () {
        $queueName = trim($this->ask('Write a queue name: '));
        if (empty($queueName)) {
            $queueName = 'default-exchange-queue';
        }

        /* @var AMQPStreamConnection $connection */
        /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        [ $connection, $channel ] = $this->setup();
        $channel->queue_declare($queueName, false, true, false, false);
        $i = 0;

        $callback = function (AMQPMessage $msg) use (&$i) {
            echo sprintf("[%'*5d] %s%s", ++$i, $msg->body, PHP_EOL);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while ( $channel->is_consuming() ) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }
}
