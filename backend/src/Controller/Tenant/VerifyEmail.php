<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Exception\TenantException;
use Backend\Utils\Encryption;
use Backend\Utils\Vars;


class VerifyEmail extends BaseController
{
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();

    if(empty($data["verifyEmail"]) && empty($data["verifyToken"])) 
      throw new TenantException('Missing fields', 308);

    $token = Encryption::Decode($data["verifyToken"], $this->secretKey);
    $token = explode("-", $token); 
    if(!isset($token[0]) || !isset($token[1]))
      throw new TenantException('Token faild!', 308);
    
    if ($token[0] != $data["verifyEmail"]) 
      throw new TenantException('E-Mail verification error!', 308);

    $user = $this->tenantRepository->getTenant($data["verifyEmail"]);
    if ($user->status != Vars::TENANT_STATUS_PENDING) 
      throw new TenantException('E-Mail already verified!', 400);

    $this->tenantRepository->emailVerify($user->username, Vars::TENENT_STATUS_CONFIRMED);

    $data = array(
      'message'  => 'Verify Successfull',
      'username' => $user->username
    );

    return $this->jsonResponse($response, $data, 200);
  }
}