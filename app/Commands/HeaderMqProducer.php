<?php

namespace App\Commands;

use App\Extensions\AmqpConnectionChannel;
use Faker\Factory;
use LaravelZero\Framework\Commands\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class HeaderMqProducer extends Command
{
    use AmqpConnectionChannel;

    protected $signature = 'header:publisher {--auto}';

    protected $description = 'RabbitMQ header producer';

    public function handle () {
        $auto = $this->option('auto');

        if (!$auto) {
            $text = trim($this->ask('Write any message: '));
            $ex = trim($this->ask('Write an exchange name: '));
        }

        if (empty($text)) {
            $text = json_encode([
                'name'  => Factory::create()->name,
                'email' => Factory::create()->email,
            ]);
        }

        if (empty($ex)) {
            $ex = 'header.exchange';
        }

        $nType = rand(1, 100) % 2 == 0 ? 'even' : 'odd';
        $names = [ 'anik', 'syed' ];
        shuffle($names);
        $name = $names[0];

        $headers = [
            'number' => $nType,
            'name'   => $name,
        ];

        $message = new AMQPMessage($text, [
            'delivery_mode'       => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'application_headers' => new AMQPTable($headers),
        ]);

        /* @var AMQPStreamConnection $connection */
        /* @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        [ $connection, $channel ] = $this->setup();
        $channel->exchange_declare($ex, 'headers', false, true, false);

        $channel->basic_publish($message, $ex);

        $this->output->success(sprintf('Sent message: [exchange: %s] [headers: %s] - [MSG: %s]', $ex, json_encode($headers), $text));

        $channel->close();
        $connection->close();
    }
}
