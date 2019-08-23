<?php

namespace App\Commands;

use App\Extensions\AmqpConnectionChannel;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class HeaderMqConsumer extends Command
{
    use AmqpConnectionChannel;

    protected $signature = 'header:consumer {--auto} {--odd}';

    protected $description = 'RabbitMQ header consumer';

    public function handle () {
        if (!$this->option('auto')) {
            $ex = trim($this->ask('Write an exchange name: '));
            $qn = trim($this->ask('Write a queue name: '));
        }

        if (empty($ex)) {
            $ex = 'header.exchange';
        }

        if (empty($qn)) {
            $qn = 'header.exchange.queue.' . ($this->option('odd') ? 'odd' : 'even');
        }

        if ($this->option('odd')) {
            $props = [
                'x-match' => 'any',
                'number'  => 'odd',
                'name'    => 'syed',
            ];
        } else {
            $props = [
                'x-match' => 'all',
                'number'  => 'even',
                'name'    => 'anik',
            ];
        }

        /* @var AMQPStreamConnection $connection */
        /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        [ $connection, $channel ] = $this->setup();
        $channel->queue_declare($qn, false, true, false, false, false);
        $channel->queue_bind($qn, $ex, '', false, new AMQPTable($props));

        $callback = function (AMQPMessage $msg) use ($qn, $ex, $props) {
            $this->output->success(sprintf("[ex: %s] [queue: %s] [headers: %s] - [MSG: %s] - [Msg-Header: %s]", $ex, $qn, json_encode($props), $msg->body, json_encode($msg->get_properties()['application_headers']->getNativeData())));
        };

        $channel->basic_consume($qn, '', false, true, false, false, $callback);

        $this->output->warning(sprintf('Waiting for messages [%s] - [%s] - [%s]', $ex, $qn, json_encode($props)));

        while ( $channel->is_consuming() ) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
