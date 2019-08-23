<?php

namespace App\Commands;

use App\Extensions\AmqpConnectionChannel;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DefaultMqConsumer extends Command
{
    use AmqpConnectionChannel;

    protected $signature = 'default:consumer {--auto}';

    protected $description = 'RabbitMQ default receiver';

    public function handle () {
        if (!$this->option('auto')) {
            $qn = trim($this->ask('Write a queue name: '));
        }

        if (empty($qn)) {
            $qn = 'default.exchange.queue';
        }

        /* @var AMQPStreamConnection $connection */
        /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        [ $connection, $channel ] = $this->setup();
        $channel->queue_declare($qn, false, true, false, false);

        $callback = function (AMQPMessage $msg) use ($qn) {
            $this->output->success(sprintf("[queue: %s] - [MSG: %s]", $qn, $msg->body));
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($qn, '', false, true, false, false, $callback);

        $this->output->warning('Waiting for messages [' . $qn . ']');

        while ( $channel->is_consuming() ) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
