<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Entity\TenantEntity;
use Backend\Exception\TenantException;
use Backend\Utils\Rabbitmq;
use Backend\Utils\Encryption;
use Backend\Utils\Vars;


class RecoverPassword extends BaseController
{
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();

    if (empty($data["username"]) || empty($data["token"]))
      throw new TenantException('Missing fields!', 308);

    $token = Encryption::Decode($data["token"], $this->secretKey);
    $token = explode("-", $token); 
    if(!isset($token[0]) || !isset($token[1]))
      throw new TenantException('Token faild!', 308);

    if ($token[0] != $data["username"]) 
      throw new TenantException('Token verification error!', 308);

    if (empty($data["password"]) || strlen($data["password"]) < 4) 
      throw new TenantException('Password required!', 400);

    if (time() - (int) $token[1] > 9900)
      throw new TenantException('Token expired!', 400);

    $user     = $this->tenantRepository->getTenant($data["username"]);
    $password = password_hash($data["password"], PASSWORD_BCRYPT);
    $this->tenantRepository->updateTenantPassword($data["username"], $password);
    if($user->status == Vars::TENANT_STATUS_PENDING)
      $this->tenantRepository->emailVerify($data["username"], Vars::TENANT_STATUS_CONFIRMED);

    $data = array(
      'message' => 'Password has been changed!'
    );

    return $this->jsonResponse($response, $data, 200);
  }
}