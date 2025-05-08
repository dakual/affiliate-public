<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Entity\TenantEntity;
use Backend\Exception\TenantException;
use Backend\Utils\Vars;


class Register extends BaseController
{
  
  public function __invoke(Request $request, Response $response): Response
  {
    $data  = (array) $request->getParsedBody();

    if (! isset($data["username"]) || strlen($data["username"]) < 3)
      throw new TenantException('The field "username" is required.', 400);

    if (! isset($data["password"]) || strlen($data["password"]) < 4)
      throw new TenantException('The field "password" is required.', 400);

    if (! isset($data["name"]) || strlen($data["name"]) < 3)
      throw new TenantException('The field "name" is required.', 400);

    $this->tenantRepository->checkTenantByUsername($data["username"]);

    $user = new TenantEntity();
    $user->name     = $data["name"];
    $user->username = $data["username"];
    $user->company  = @$data["company"];
    $user->domain   = "aff-".time()."-".rand(111111, 999999);
    $user->password = password_hash($data["password"], PASSWORD_BCRYPT);

    $user  = $this->tenantRepository->createUser($user, Vars::TENANT_STATUS_PENDING);
    $token = $this->getToken($user->username);

    $responseData = array(
      'message'  => 'Create successfully',
      'username' => $user->username,
      'token'    => $token
    );

    $this->sendEmail($user, $token);

    return $this->jsonResponse($response, $responseData, 200);
  }

  public function sendEmail(TenantEntity $user) 
  {
    $token   = $this->getToken($user->username);
    $link    = $this->adminURL . '/login?verifyEmail=' . $user->username . '&verifyToken=' . $token;
    $message = array(
      "recipient" => array("userId" => $user->id, "name" => $user->name, "email" => $user->username),
      "data"      => array("confirmLink" => $link),
      "template"  => array("name" => "admin-register-confirmation", "subject" => "Verify your Email Address", "lanuage" => "en")
    );

    $this->messageQueue->sendQueue("email", json_encode($message));
    $this->logger->info('Admin email confrim request. Email sent the queue.', ["name" => $user->username]);
  }

}