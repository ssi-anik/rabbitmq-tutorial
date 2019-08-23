<?php

namespace App\Commands;

use App\Extensions\AmqpConnectionChannel;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DirectQosMqConsumer extends Command
{
    use AmqpConnectionChannel;

    protected $signature = 'direct:consumer:qos {--auto}';

    protected $description = 'RabbitMQ direct consumer with QoS ACK';

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
            $qn = 'direct.exchange.qos.queue';
        }

        /* @var AMQPStreamConnection $connection */
        /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        [ $connection, $channel ] = $this->setup();
        $channel->queue_declare($qn, false, true, false, false);
        $channel->queue_bind($qn, $ex, $bk);

        $callback = function (AMQPMessage $msg) use ($qn, $ex, $bk) {
            $this->output->success(sprintf("[ex: %s] [queue: %s] [bind: %s] - [MSG: %s]", $ex, $qn, $bk, $msg->body));
            $chosen = $this->choice('Acknowledge message received? ', [
                'Ack',
                'Reject - No requeue',
                'Reject - Requeue',
                'Neg Ack - No requeue',
                'Neg Ack - Requeue',
            ]);

            switch ( $chosen ) {
                case 'Ack':
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    break;
                case 'Reject - No requeue':
                    $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], false);
                    break;
                case 'Reject - Requeue':
                    $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], true);
                    break;
                case 'Neg Ack - No requeue':
                    $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, false);
                    break;
                case 'Neg Ack - Requeue':
                    $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, true);
                    break;
            }

            $this->output->success('Waiting for next message to arrive.');
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($qn, '', false, false, false, false, $callback);

        $this->output->warning(sprintf('Waiting for messages [%s] - [%s] - [%s]', $ex, $qn, $bk));

        while ( $channel->is_consuming() ) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
