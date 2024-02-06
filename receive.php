<?php

// Inclui o arquivo de autoload da biblioteca Composer
require_once __DIR__ . '/vendor/autoload.php';

// Usa a classe AMQPStreamConnection da biblioteca PhpAmqpLib
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

// Cria uma conexão com o servidor RabbitMQ
$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'root');

// Cria um canal de comunicação com o RabbitMQ
$channel = $connection->channel($channel_id = null);

// Declara uma fila chamada 'hello'
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

// Imprime uma mensagem indicando que o consumidor está esperando por mensagens
echo " [*] Waiting for messages. To exit press CTRL+C\n";

// Define uma função de callback para processar as mensagens recebidas
$callback = function (AMQPMessage $msg) use($queue) {
    try {
        $payload = $msg->getBody();
        // $channel = $msg->getChannel();

        echo " [x] Received: '".$payload."'\n";

        // Simula um procedimento que pode gerar uma exceção
        if (rand(0, 1) == 1) {
            // Faz o procedimento com sucesso
            $msg->ack();
        } else {
            // Lança uma exceção para simular um erro
            throw new Exception("[!] Erro ao processar a mensagem.\n");
        }
        
    } catch (\Throwable $exception) {
        echo json_encode($msg->get_properties());

        if ($msg->has($propertie = 'application_headers')) {
            $application_headers = $msg->get($propertie);
        }

        
        // Obtém o número de vezes que a mensagem foi rejeitada
        $rejectCount = @$application_headers['x-reject-count'] ?? 0;

        if ($rejectCount < 3) {
            // Incrementa o contador de rejeições e reenfileira a mensagem na fila 'retry'
            $rejectCount++;
            $msg->set('application_headers', new AMQPTable(['x-reject-count' => $rejectCount]));

            // Rejeita a mensagem, e reenfileira na fila original
            $msg->reject($requeue = true);

            echo " [!] Message rejected and requeued. Retry count: $rejectCount\n";
        } else {
            // Excede o número máximo de tentativas e descarta a mensagem
            echo " [!] Message discarded after $rejectCount retries.\n";

            // Rejeita a mensagem, mas não a reenfileira na fila original
            $msg->ack($requeue = false); // caso queira pode enviar para uma fila nova
        }

        // Em caso de erro, imprime a mensagem de erro
        echo $exception->getMessage();
    }
};

// Informa ao canal que deve consumir mensagens da fila 'hello' usando a função de callback
$channel->basic_consume(
    $queue = 'hello', 
    $consumer_tag = '', 
    $no_local = false, 
    $no_ack = false, // Agora, desativamos o envio automático do ack
    $exclusive = false,
    $nowait = false, 
    $callback = $callback,
    $ticket = null,
    $arguments = array()
);

// Tenta esperar por mensagens até que ocorra um erro ou o processo seja interrompido
try {
    $channel->consume();
} catch (\Throwable $exception) {
    // Em caso de erro, imprime a mensagem de erro
    echo $exception->getMessage();
}

// Fecha o canal e a conexão com o RabbitMQ
$channel->close();
$connection->close();
