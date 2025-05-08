<?php
namespace Backend\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Exception\UserException;
use Backend\Utils\Encryption;
use Firebase\JWT\JWT;
use Backend\Utils\Vars;

class Login extends BaseController
{
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();

    if (empty($data['username']))
      throw new UserException('Username and Password required!', 400);

    if (empty($data['domain']))
      throw new UserException('Domain required!', 400);

    $user = $this->userRepository->checkUser($data["username"], $data['domain']);
    $data = array(
      'message'  => 'Please sign-in.',
      'action'   => 'login',
      'data'     => $user 
    );     

    return $this->jsonResponse($response, $data, 200);
  }
}