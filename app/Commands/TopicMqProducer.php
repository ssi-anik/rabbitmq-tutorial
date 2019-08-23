<?php

namespace App\Commands;

use App\Extensions\AmqpConnectionChannel;
use Faker\Factory;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TopicMqProducer extends Command
{
    use AmqpConnectionChannel;

    protected $signature = 'topic:publisher {--auto}';

    protected $description = 'RabbitMQ topic producer';

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
            $ex = 'topic.exchange';
        }

        if (empty($r)) {
            $r = 'topic.routing.key';
        }

        $message = new AMQPMessage($text, [ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ]);

        /* @var AMQPStreamConnection $connection */
        /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        [ $connection, $channel ] = $this->setup();
        $channel->exchange_declare($ex, 'topic', false, true, false);

        $channel->basic_publish($message, $ex, $r);

        $this->output->success(sprintf('Sent message: [exchange: %s] [routing: %s] - [MSG: %s]', $ex, $r, $text));

        $channel->close();
        $connection->close();
    }
}
