RabbitMQ tutorial for anik/amqp
---

## Installation
- `cp .env.example .env`
- Populate the required environment variables.
- `sudo chown -R 1001:1001 ~/.backup/rabbitmq/rabbitmq-tutorial`
- It comes with docker. `docker-compose up -d --build`
- `docker-compose exec php bash`
- `composer install`
- `php artisan <command to run>`


## Available commands
**Adding `--auto` to all the following commands will not ask for message, exchange, queue, routing and binding key. Or empty values for the prompted questions will fill up data automatically.**
 
- `php artisan amqp:publisher:dynamic`
- `php artisan amqp:publisher:class`
- `php artisan amqp:publisher:class:headers` (Sends headers to message)

- `php artisan amqp:consumer:closure`
- `php artisan amqp:consumer:class`
- `php artisan amqp:consumer:closure:headers`
