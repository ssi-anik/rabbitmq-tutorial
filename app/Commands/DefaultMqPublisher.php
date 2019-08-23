<?php

namespace App\Commands;

use App\Extensions\AmqpConnectionChannel;
use Faker\Factory;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DefaultMqPublisher extends Command
{
    use AmqpConnectionChannel;

    protected $signature = 'default:publisher {--auto}';

    protected $description = 'RabbitMQ default sender';

    public function handle () {
        $auto = $this->option('auto');

        if (!$auto) {
            $text = trim($this->ask('Write any message: '));
            $qn = trim($this->ask('Write a queue name: '));
        }

        if (empty($text)) {
            $text = json_encode([
                'name'  => Factory::create()->name,
                'email' => Factory::create()->email,
            ]);
        }

        if (empty($qn)) {
            $qn = 'default.exchange.queue';
        }

        $message = new AMQPMessage($text, [ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ]);

        /* @var AMQPStreamConnection $connection */
        /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        [ $connection, $channel ] = $this->setup();
        $channel->queue_declare($qn, false, true, false, false);
        $channel->basic_publish($message, '', $qn);

        $this->output->success(sprintf('Sent message: [queue: %s] - [%s]', $qn, $text));

        $channel->close();
        $connection->close();
    }
}
