<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Entity\TenantEntity;
use Backend\Exception\TenantException;
use Backend\Utils\Rabbitmq;
use Backend\Utils\Encryption;


class ForgotPassword extends BaseController
{
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();

    if (! isset($data["username"]) || strlen($data["username"]) < 4) 
      throw new TenantException('The field "username" is required.', 400);

    $user = $this->tenantRepository->getTenant($data["username"]);
    if(time() - strtotime($user->timestamp) < 60) 
      throw new TenantException('Please wait 1 minute to resend again!', 400);

    $this->sendEmail($user);
    $this->tenantRepository->updateTimestamp($user->username);

    $data = array(
      'message' => 'Password reset Email has been sent!'
    );

    return $this->jsonResponse($response, $data, 200);
  }

  public function sendEmail(TenantEntity $user) {
    $token   = $this->getToken($user->username);
    $link    = $this->adminURL . '/recover-password?recoveryEmail=' . $user->username . '&recoveryToken=' . $token;
    $message = array(
      "recipient" => array("userId" => $user->id, "name" => $user->name, "email" => $user->username),
      "data"      => array("confirmLink" => $link),
      "template"  => array("name" => "admin-forgot-password", "subject" => "Reset Password", "lanuage" => "en")
    );

    $this->messageQueue->sendQueue("email", json_encode($message));
    $this->logger->info('Admin reset password request. Email sent the queue.', ["name" => $user->username]);
  }
}