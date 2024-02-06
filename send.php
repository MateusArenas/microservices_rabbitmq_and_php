<?php

$payload = @$_GET['msg'] ?? 'Hello World!';

// Inclui o arquivo de autoload gerado pelo Composer
require_once __DIR__ . '/vendor/autoload.php';

// Usa as classes necessárias da biblioteca PhpAmqpLib
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

// Cria uma conexão com o servidor RabbitMQ
$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'root');

// Cria um canal de comunicação com o RabbitMQ
$channel = $connection->channel($channel_id = null);

// Declara uma fila chamada 'hello' no canal
$channel->queue_declare(
    $queue = 'hello', 
    $passive = false,
    $durable = false,
    $exclusive = false,
    $auto_delete = false,
    $nowait = false,
    $arguments = array(),
    $ticket = null
);

// Cria uma mensagem AMQP com o corpo 'Hello World!'
$msg = new AMQPMessage(
    $body = $payload, 
    $properties = array(
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        // Cria um objeto AMQPTable para representar os cabeçalhos da aplicação
        'application_headersz' => new AMQPTable([ 'x-reject-count' => 0 ]),
    )
);

// Publica a mensagem na fila 'hello' no canal
$channel->basic_publish(
    $msg, 
    $exchange = '',
    $routing_key = 'hello',
    $mandatory = false,
    $immediate = false,
    $ticket = null
);

// Imprime uma mensagem indicando que a mensagem foi enviada com sucesso
echo " [x] Sent '". $payload ."'\n";

// Fecha o canal e a conexão com o RabbitMQ
$channel->close();
$connection->close();

