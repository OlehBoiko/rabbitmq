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


$connection = new AMQPStreamConnection(
    HOST,
    PORT,
    USER,
    PASS,
    VHOST,
    false,
    'AMQPLAIN',
    null,
    'en_US',
    3.0,
    120.0,
    null,
    true,
    60.0
);

$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);
$channel->exchange_declare($exchange, 'direct', false, true, false);
$channel->queue_bind($queue, $exchange);

/**
 * @param AMQPMessage $message
 */
function process_message(AMQPMessage $message)
{
    $messageBody =  json_decode($message->body);

    usleep(5000);

    mail(
        $messageBody->email,
        $messageBody->email.' Subscribed', $messageBody->email . ' has subscribed to your chanel' . PHP_EOL .
        $message->body
    );

    file_put_contents(dirname(__DIR__).'/emails/'.$messageBody->email, $message->body);

    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

}
$consumerTag = 'local.ubuntu.consumer';

$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');


/**
 *
 * @param \PhpAmqpLib\Channel\AMQPChannel $channel
 * @param \PhpAmqpLib\Connection\AbstractConnection $connection
 */
function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}


register_shutdown_function('shutdown', $channel, $connection);


while (count($channel->callbacks)) {
    $channel->wait();
}