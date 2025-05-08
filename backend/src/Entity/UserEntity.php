<?php
namespace Backend\Entity;

class UserEntity
{
  public string $id;
  public string $username;
  public string $password;
  public string $name;
  public string $created_at;
  public string $updated_at;
  public string $status;

  public function toJson(): object
  {
    return json_decode((string) json_encode(get_object_vars($this)), false);
  }
}