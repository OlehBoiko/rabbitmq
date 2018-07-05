<?php
/**
 * PHP 7.0
 * Created by PhpStorm.
 * @author: Oleh Boiko
 * @date  : 7/5/18
 * @email boikoovdal@gmail.com
 */

require dirname(__DIR__, 1) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

define('HOST', 'wolverine-01.rmq.cloudamqp.com');
define('PORT', 5672);
define('USER', 'ijknhfog');
define('PASS', 'j3CxeY4A8hvCy8500wSiSmvEd41MH5Hc');
define('VHOST', 'ijknhfog');

$exchange = 'subscribers';
$queue = 'gurucoder_subscribers';


$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);
$channel->exchange_declare($exchange, 'direct', false, true, false);
$channel->queue_bind($queue, $exchange);

$faker = Faker\Factory::create();

$limit = 20000;
$iteration = 0;

while ($iteration < $limit) {
    $messageBody = json_encode([
        'name'       => $faker->name,
        'email'      => $faker->email,
        'address'    => $faker->address,
        'subscribed' => true,
    ]);

    $message = new AMQPMessage(
        $messageBody,
        [
            'content_type'  => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]
    );

    $channel->basic_publish($message, $exchange);

//    echo 'Published message to queue: ' . $queue . PHP_EOL;
//    var_dump($messageBody);

    $iteration++;
}

echo 'Finished publishing to queue: ' . $queue . PHP_EOL;

$channel->close();
$connection->close();