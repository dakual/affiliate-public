<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Cookies;
use GuzzleHttp\Client;

abstract class BaseController
{
  public $BE_HOST;
  public $BE_PORT;
  public $BE_VERSION;
  public $secretKey;

  public function __construct()
  {
    $this->BE_HOST    = getenv('BE_HOST');
    $this->BE_PORT    = getenv('BE_PORT');
    $this->BE_VERSION = getenv('BE_VERSION');
    $this->secretKey  = getenv('SECRET_KEY');
  }

  protected function apiCall(string $path, array $data): array 
  {
    $client = new Client([
      'base_uri' => "http://{$this->BE_HOST}:{$this->BE_PORT}/{$this->BE_VERSION}/",
      'timeout'  => 2.0,
    ]);

    $token = @$_COOKIE['_token'];
    $apiResponse = $client->request('POST', $path, [
      'json'    => $data,
      'headers' => [
          'User-Agent'    => 'affiliate/1.0',
          'Authorization' => 'Bearer ' . $token,
          'Accept'        => 'application/json',
      ]
    ]);

    $apiStatus = $apiResponse->getStatusCode();
    $apiBody   = $apiResponse->getBody();

    return json_decode($apiBody, true);
  }

  protected function setJwtCookie(Request $request, Response $response, Array $data): Response 
  {
    $cookies  = new Cookies();
    $cookies->setDefaults([
      'hostonly' => false, 
      'secure'   => false, 
      'httponly' => true, 
      'sameSite' => 'None'
    ]);

    $cookies->set('_token', [
      'value'    => $data['token'],
      'path'     => '/', // $request->getUri()->getHost(),
      // 'samesite' => 'Strict',
      'expires'  => $data['expire'],
    ]);
    
    return $response->withHeader('Set-Cookie', $cookies->toHeaders());
  }
}