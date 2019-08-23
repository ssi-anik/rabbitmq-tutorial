<?php

namespace App\Commands;

use App\Extensions\AmqpConnectionChannel;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TopicMqConsumer extends Command
{
    use AmqpConnectionChannel;

    protected $signature = 'topic:consumer {--auto}';

    protected $description = 'RabbitMQ topic consumer';

    public function handle () {
        if (!$this->option('auto')) {
            $ex = trim($this->ask('Write an exchange name: '));
            $qn = trim($this->ask('Write a queue name: '));
            $bk = trim($this->ask('Write a binding key: '));
        }

        if (empty($ex)) {
            $ex = 'topic.exchange';
        }

        if (empty($bk)) {
            $bk = 'topic.#';
        }

        if (empty($qn)) {
            $qn = 'topic.exchange.queue';
        }

        /* @var AMQPStreamConnection $connection */
        /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        [ $connection, $channel ] = $this->setup();
        $channel->queue_declare($qn, false, true, false, false);
        $channel->queue_bind($qn, $ex, $bk);

        $callback = function (AMQPMessage $msg) use ($qn, $ex, $bk) {
            $this->output->success(sprintf("[ex: %s] [queue: %s] [bind: %s] - [MSG: %s]", $ex, $qn, $bk, $msg->body));
        };

        $channel->basic_consume($qn, '', false, true, false, false, $callback);

        $this->output->warning(sprintf('Waiting for messages [%s] - [%s] - [%s]', $ex, $qn, $bk));

        while ( $channel->is_consuming() ) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
