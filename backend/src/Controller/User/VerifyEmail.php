<?php
namespace Backend\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Exception\UserException;
use Backend\Utils\Encryption;
use Backend\Utils\Vars;


class VerifyEmail extends BaseController
{
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();

    if(empty($data["verifyEmail"]) && empty($data["verifyToken"])) 
      throw new UserException('Missing fields', 308);

    $token = Encryption::Decode($data["verifyToken"], $this->secretKey);
    $token = explode("-", $token); 
    if(!isset($token[0]) || !isset($token[1]))
      throw new UserException('Token faild!', 308);
    
    if ($token[0] != $data["verifyEmail"]) 
      throw new UserException('E-Mail verification error!', 308);

    $user = $this->userRepository->getUser($data["verifyEmail"]);
    if ($user->status != Vars::USER_STATUS_PENDING) 
      throw new UserException('E-Mail already verified!', 400);

    $this->userRepository->emailVerify($user->username, Vars::USER_STATUS_CONFIRMED);

    $data = array(
      'message'  => 'Verify Successfull',
      'username' => $user->username
    );

    return $this->jsonResponse($response, $data, 200);
  }
}