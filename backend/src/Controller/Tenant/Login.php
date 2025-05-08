<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Exception\TenantException;
use Backend\Utils\Encryption;
use Firebase\JWT\JWT;
use Backend\Utils\Vars;

class Login extends BaseController
{
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();
    // $data = json_decode(json_encode($data), false);

    if (empty($data['username']) || empty($data['password']))
      throw new TenantException('Username and Password required!', 400);

    $user = $this->tenantRepository->login($data["username"]);
    if (!password_verify($data["password"], $user->password))
      throw new TenantException('Username or password incorrect!', 400);

    if ($user->status == Vars::TENANT_STATUS_PENDING) 
      throw new TenantException('E-mail verification required!', 400);

    $expire = time() + ((int) $this->jwtLifeTime);
    if(isset($data["remember"]) && $data["remember"] == 'on')
      $expire = time() + ((int) $this->jwtLifeTime * 30);

    $token = [
      "iss"  => $this->jwtIssuer,
      "aud"  => $this->siteURL,
      "sub"  => hash('sha256', $user->id),
      "iat"  => time(),
      "exp"  => $expire
    ];

    $jwt  = JWT::encode($token, $this->jwtPrivateKey, 'RS256');
    $data = array(
      'message'  => 'Login Successfull',
      'username' => $user->username,
      'token'    => $jwt,
      'expire'   => $expire
    );

    return $this->jsonResponse($response, $data, 200);
  }
}