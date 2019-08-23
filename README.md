RabbitMQ tutorial for PHP
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
**Adding `--auto` to all the following commands will not ask for message, exchange, queue, routing and binding key**
**Empty values for the prompted questions will fill up data automatically**
 
- `php artisan default:publisher`
- `php artisan default:consumer`

- `php artisan direect:publisher`
- `php artisan direect:consumer`

- `php artisan direct:consumer:qos`

- `php artisan topic:publisher`
- `php artisan topic:consumer`

- `php artisan header:publisher`
- `php artisan header:consumer`
