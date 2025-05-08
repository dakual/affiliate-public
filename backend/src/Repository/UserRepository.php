<?php
namespace Backend\Repository;

use Backend\Entity\UserEntity;
use Backend\Exception\UserException;

class UserRepository extends BaseRepository
{
  public function createUser(UserEntity $user, string $status): UserEntity
  {
    $query     = 'INSERT INTO `Users` (`username`, `password`, `name`, `status`) VALUES (:username, :password, :name, :status)';
    $statement = $this->database->prepare($query);

    $statement->bindParam('username', $user->username);
    $statement->bindParam('password', $user->password);
    $statement->bindParam('name', $user->name);
    $statement->bindParam('status', $status);
    $statement->execute();

    return $this->getUser($user->username);
  }

  public function checkUser(string $username, string $domain)
  {
    $query     = 'SELECT u.*, COUNT(t.id) as affilate FROM Users u LEFT JOIN AffiliateTenants a ON a.user_id = u.id LEFT JOIN Tenants t ON t.id = a.tenant_id AND t.`domain` = :domain WHERE u.username = :username;';
    $statement = $this->database->prepare($query);
    $statement->bindParam('username', $username);
    $statement->bindParam('domain', $domain);
    $statement->execute();
    
    return $statement->fetchObject();
  }

  public function getUser(string $username): UserEntity
  {
    $query     = 'SELECT * FROM `Users` WHERE `username` = :username';
    $statement = $this->database->prepare($query);
    $statement->bindParam('username', $username);
    $statement->execute();
    $user = $statement->fetchObject(UserEntity::class);
    if (!$user) {
        throw new UserException('User not found!', 404);
    }

    return $user;
  }

  public function checkUserByUsername(string $username): void
  {
      $query     = 'SELECT * FROM `Users` WHERE `username` = :username';
      $statement = $this->database->prepare($query);
      $statement->bindParam('username', $username);
      $statement->execute();
      $user = $statement->fetchObject();
      if ($user) {
          throw new UserException('Username already exists.', 400);
      }
  }

  public function emailVerify(string $username, string $status): void
  {
    $updated   = date("Y-m-d H:i:s");
    $query     = 'UPDATE `Users` SET `updated_at` = :updated, `status` = :status WHERE `username` = :username';
    $statement = $this->database->prepare($query);
    $statement->bindParam('updated', $updated);
    $statement->bindParam('status', $status);
    $statement->bindParam('username', $username);
    $statement->execute();
  }
}