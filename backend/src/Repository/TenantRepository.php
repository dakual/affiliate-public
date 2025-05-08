<?php
namespace Backend\Repository;

use Backend\Entity\TenantEntity;
use Backend\Exception\TenantException;

class TenantRepository extends BaseRepository
{
  public function login(string $username): TenantEntity
  {
    $query = 'SELECT * FROM `Tenants` WHERE `username` = :username ORDER BY `id`';
    $statement = $this->database->prepare($query);
    $statement->bindParam('username', $username);
    $statement->execute();

    $user = $statement->fetchObject(TenantEntity::class);
    if (! $user) {
        throw new TenantException(
            'Username or password incorrect!', 400
        );
    }

    return $user;
  }

  public function createUser(TenantEntity $tenant, string $status): TenantEntity
  {
    $query     = 'INSERT INTO `Tenants` (`username`, `password`, `name`, `company`,`domain`,`status`) VALUES (:username, :password, :name, :company, :domain, :status)';
    $statement = $this->database->prepare($query);

    $statement->bindParam('username', $tenant->username);
    $statement->bindParam('password', $tenant->password);
    $statement->bindParam('name', $tenant->name);
    $statement->bindParam('company', $tenant->company);
    $statement->bindParam('domain', $tenant->domain);
    $statement->bindParam('status', $status);
    $statement->execute();

    return $this->getTenant($tenant->username);
  }

  public function getTenant(string $username): TenantEntity
  {
    $query     = 'SELECT * FROM `Tenants` WHERE `username` = :username';
    $statement = $this->database->prepare($query);
    $statement->bindParam('username', $username);
    $statement->execute();
    $user = $statement->fetchObject(TenantEntity::class);
    if (! $user) {
        throw new TenantException('User not found!', 404);
    }

    return $user;
  }

  public function checkTenantByUsername(string $username): void
  {
      $query     = 'SELECT * FROM `Tenants` WHERE `username` = :username';
      $statement = $this->database->prepare($query);
      $statement->bindParam('username', $username);
      $statement->execute();
      $user = $statement->fetchObject();
      if ($user) {
          throw new TenantException('Username already exists.', 400);
      }
  }

  public function updateTimestamp(string $username): void
  {
    $timestamp = date("Y-m-d H:i:s");
    $query     = 'UPDATE `Tenants` SET `timestamp` = :timestamp WHERE `username` = :username';
    $statement = $this->database->prepare($query);
    $statement->bindParam('timestamp', $timestamp);
    $statement->bindParam('username', $username);
    $statement->execute();
  }

  public function emailVerify(string $username, string $status): void
  {
    $updated   = date("Y-m-d H:i:s");
    $query     = 'UPDATE `Tenants` SET `updated_at` = :updated, `status` = :status WHERE `username` = :username';
    $statement = $this->database->prepare($query);
    $statement->bindParam('updated', $updated);
    $statement->bindParam('status', $status);
    $statement->bindParam('username', $username);
    $statement->execute();
  }

  public function updateTenantPassword(string $username, string $password): void
  {
    $updated   = date("Y-m-d H:i:s");
    $query     = 'UPDATE `Tenants` SET `updated_at` = :updated, `password` = :password WHERE `username` = :username';
    $statement = $this->database->prepare($query);
    $statement->bindParam('updated', $updated);
    $statement->bindParam('password', $password);
    $statement->bindParam('username', $username);
    $statement->execute();
  }

  public function getTenantByHash(string $userIdHash): TenantEntity
  {
    $query     = 'SELECT * FROM `Tenants` WHERE SHA2(`id`, 256) = :id';
    $statement = $this->database->prepare($query);
    $statement->bindParam('id', $userIdHash);
    $statement->execute();
    $user = $statement->fetchObject(TenantEntity::class);
    unset($user->password);
    if (! $user) {
        throw new TenantException('User not found!', 404);
    }

    return $user;
  }

  public function getTenantInfo(string $domain): TenantEntity
  {
    $query     = 'SELECT * FROM `Tenants` WHERE `domain` = :domain';
    $statement = $this->database->prepare($query);
    $statement->bindParam('domain', $domain);
    $statement->execute();
    $user = $statement->fetchObject(TenantEntity::class);
    unset($user->password);
    if (! $user) {
        throw new TenantException('Tenant not found!', 404);
    }

    return $user;
  }
}