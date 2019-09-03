<?php

namespace App\Extensions;

use Anik\Amqp\ConsumableMessage;

class CustomConsumableMessage extends ConsumableMessage
{
    public function handle () {
        echo $this->getStream() . PHP_EOL;
        $this->getDeliveryInfo()->acknowledge();
    }
}
