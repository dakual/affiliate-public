<?php
namespace Backend\Utils;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbitmq implements MQInterface 
{
  private $connection;
  private $channel;
  private $host;
  private $user;
  private $pass;
  private $port;

  public function __construct()
  {
    $this->host = getenv('MQ_HOST');
    $this->port = getenv('MQ_PORT');
    $this->user = getenv('MQ_USER');
    $this->pass = getenv('MQ_PASS');
  }

  private function openConnection()
  {
    $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass);
  }

  private function sendMessage(string $channel, String $message)
  {
    $this->channel = $this->connection->channel();
    $this->channel->exchange_declare($channel, 'direct', false, false, false);
    $this->channel->queue_declare($channel, true, false, false, false);
    $this->channel->queue_bind($channel, $channel, $channel);
    $this->channel->basic_publish(new AMQPMessage($message), $channel, $channel);
  }

  private function close()
  {
    $this->channel->close();
    $this->connection->close();
  }

  public function sendQueue(string $queueName, string $data)
  {
    switch ($queueName) {
      case "email":
        $this->openConnection();
        $this->sendMessage($queueName, $data);
        $this->close();
        break;
      default:
        throw new TenantException('Wrong channel for message queue!', 400);
    }
  }
}