<?php
namespace Backend\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Entity\UserEntity;
use Backend\Exception\UserException;
use Backend\Utils\Vars;


class Register extends BaseController
{
  
  public function __invoke(Request $request, Response $response): Response
  {
    $data  = (array) $request->getParsedBody();

    if (! isset($data["username"]) || strlen($data["username"]) < 3)
      throw new UserException('The field "username" is required.', 400);

    if (! isset($data["password"]) || strlen($data["password"]) < 4)
      throw new UserException('The field "password" is required.', 400);

    if (! isset($data["name"]) || strlen($data["name"]) < 3)
      throw new UserException('The field "name" is required.', 400);

    $this->userRepository->checkUserByUsername($data["username"]);

    $user = new UserEntity();
    $user->name     = $data["name"];
    $user->username = $data["username"];
    $user->password = password_hash($data["password"], PASSWORD_BCRYPT);

    $user  = $this->userRepository->createUser($user, Vars::USER_STATUS_PENDING);
    $token = $this->getToken($user->username);

    $responseData = array(
      'message'  => 'Create successfully',
      'username' => $user->username,
      'token'    => $token
    );

    $this->sendEmail($user);

    return $this->jsonResponse($response, $responseData, 200);
  }

  private function sendEmail(UserEntity $user) 
  {
    $token   = $this->getToken($user->username);
    $link    = $this->userURL . '/login?verifyEmail=' . $user->username . '&verifyToken=' . $token;
    $message = array(
      "recipient" => array("userId" => $user->id, "name" => $user->name, "email" => $user->username),
      "data"      => array("confirmLink" => $link),
      "template"  => array("name" => "user-register-confirmation", "subject" => "Verify your Email Address", "lanuage" => "en")
    );

    $this->messageQueue->sendQueue("email", json_encode($message));
    $this->logger->info('User email confrim request. Email sent the queue.', ["name" => $user->username]);
  }
}