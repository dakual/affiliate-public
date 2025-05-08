<?php
namespace Backend\Entity;

class TenantEntity
{
  public string $id;
  public string $email;
  public string $password;
  public string $name;
  public ?string $company;
  public ?string $domain;
  public string $created_at;
  public string $updated_at;
  public ?string $timestamp;
  public string $status;

  public function toJson(): object
  {
    return json_decode((string) json_encode(get_object_vars($this)), false);
  }
}