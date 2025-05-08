<?php
namespace Backend\Repository;

use \PDO;
use Backend\Exception\GlobalException;

abstract class BaseRepository
{
  private $host;
  private $user;
  private $pass;
  private $dbname;

  public function __construct()
  {
    $this->host   = getenv('MYSQL_HOST');
    $this->dbname = getenv('MYSQL_DATABASE');
    $this->user   = getenv('MYSQL_USER');
    $this->pass   = getenv('MYSQL_PASSWORD');

    try {
      $connection_str = "mysql:host={$this->host};dbname={$this->dbname}";
      $this->database = new PDO($connection_str, $this->user, $this->pass);
      $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(\Exception $ex) {
      throw new GlobalException(
        'Database failed: ' . $ex->getMessage(), 400
      );
    }
  }

  protected function getDb(): PDO
  {
      return $this->database;
  }
}