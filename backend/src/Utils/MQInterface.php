<?php
namespace Backend\Utils;

interface MQInterface {
  public function sendQueue(string $queueName, string $data);
}