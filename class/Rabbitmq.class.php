<?php 
// Inclui o arquivo de autoload gerado pelo Composer
// require_once __DIR__ . '/vendor/autoload.php';
require 'vendor/autoload.php';

// Usa as classes necessárias da biblioteca PhpAmqpLib
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbitmq
{
    /**
     * Variável que contém o endereço do rabbitmq
     * @access private
     * @name $host
     */
    private $host = 'localhost';

    /**
     * Variável que contém a porta do endereço do rabbitmq
     * @access private
     * @name $port
     */
    private $port = 5672;

    /**
     * Variável que contém o usuário do rabbitmq
     * @access private
     * @name $user
     */
    private $user = 'root';

    /**
     * Variável que contém a senha do rabbitmq
     * @access private
     * @name $password
     */
    private $password = 'root';

    private $connection;
    private $channel;

    function __construct()
    {
        // Cria uma conexão com o servidor RabbitMQ
        $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password);
        // Cria um canal de comunicação com o RabbitMQ
        $this->channel = $this->connection->channel();
    }

    public function simple_queue_declare(string $queue, $passive, $durable, $exclusive, $auto_delete = false)
    {
        try {
            // Declara uma fila chamada 'hello' no canal
            $this->channel->queue_declare($queue, $passive, $durable, $exclusive, $auto_delete);
        } catch (\Throwable $exception) {
            throw new Exception("Erro ao declarar queue[".$queue."]");
        }
    }

    public function message(
        $msg,
        $exchange = '',
        $routing_key = '',
        $mandatory = false,
        $immediate = false,
        $ticket = null
    )
    {
        try {
            // Cria uma mensagem AMQP com o corpo 'Hello World!'
            $msg = new AMQPMessage($msg);
            // Publica a mensagem na fila 'default' no canal
            $this->channel->basic_publish($msg, $exchange, $routing_key, $mandatory, $immediate, $ticket);
        } catch (\Throwable $exception) {
            throw new Exception("Erro ao enviar mensagem para a routing_key[".$routing_key."]");
        }
    }

     /**
     * Publishes a message
     *
     * @param AMQPMessage $msg
     * @param string $exchange
     * @param string $routing_key
     * @param bool $mandatory
     * @param bool $immediate
     * @param int|null $ticket
     * @throws AMQPChannelClosedException
     * @throws AMQPConnectionClosedException
     * @throws AMQPConnectionBlockedException
     */
    public function publish(
        $msg,
        $exchange = '',
        $routing_key = '',
        $mandatory = false,
        $immediate = false,
        $ticket = null
    )
    {
        try {
            // Cria uma mensagem AMQP com o corpo 'Hello World!'
            $msg = is_string($msg) ? new AMQPMessage($msg) : $msg;
            // Publica a mensagem na fila 'default' no canal
            $this->channel->basic_publish($msg, $exchange, $routing_key, $mandatory, $immediate, $ticket);
        } catch (\Throwable $exception) {
            throw new Exception("Erro ao enviar mensagem para a routing_key[".$routing_key."]");
        }
    }

    public function consume(string $queue, $consumer_tag, $no_ack = true, $exclusive = false, $nowait = false, $callback)
    {
        try {
            // Define uma função de callback para processar as mensagens recebidas
            $callback = function ($msg) {
                echo ' [x] Received ', $msg->getBody(), "\n";
            };
            // Informa ao canal que deve consumir mensagens da fila 'default' usando a função de callback
            $this->channel->basic_consume($queue, '', false, true, false, false, $callback);
            // Tenta esperar por mensagens até que ocorra um erro ou o processo seja interrompido
            $this->channel->consume();
        } catch (\Throwable $exception) {
            // Em caso de erro, imprime a mensagem de erro
            echo $exception->getMessage();
            throw new Exception("Erro ao obter mensagens da queue[".$queue."]");
        }
    }

    public function disconnect()
    {
        // Fecha o canal e a conexão com o RabbitMQ
        $this->channel->close();
        $this->connection->close();
    }
}