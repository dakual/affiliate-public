<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Exception\TenantException;
use Backend\Utils\Encryption;
use Backend\Utils\Vars;


class RegisterResend extends BaseController
{
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();

    if(empty($data["username"]) && empty($data["token"]))
      throw new TenantException('Missing fields!', 308);

    $token = Encryption::Decode($data["token"], $this->secretKey);
    $token = explode("-", $token); 
    if(!isset($token[0]) || !isset($token[1]) || !isset($token[2]))
      throw new TenantException('Token failed!', 308);

    if($token[0] != $data["username"]) 
      throw new TenantException('Token failed!', 308);

    $user = $this->tenantRepository->getTenant($data["username"]);
    if ($user->status != Vars::TENANT_STATUS_PENDING)
      throw new TenantException('Email already verified!', 400);

    if (time() - strtotime($user->timestamp) < 60)
      throw new TenantException('Wait for 1 min to resend again!', 400);
    
    $responseData = array(
      'message'  => 'E-Mail successfull resended.',
      'username' => $user->username,
    );

    $this->sendTenantRegistrationEmail($user);
    $this->tenantRepository->updateTimestamp($user->username);

    return $this->jsonResponse($response, $responseData, 200);
  }
}