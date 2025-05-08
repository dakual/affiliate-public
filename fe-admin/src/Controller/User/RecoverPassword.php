<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class RecoverPassword extends BaseController 
{

  public function __invoke(Request $request, Response $response): Response
  {
    $view   = Twig::fromRequest($request);
    $method = $request->getMethod();
    $body   = (array) $request->getParsedBody();
    $params = $request->getQueryParams();
    $data   = [];

    if($method == 'POST') 
    {
      if(empty($body["recoveryEmail"]) || empty($body["recoveryToken"]))
        return $response->withHeader('Location', '/login')->withStatus(302);

      $apiResponse = $this->apiCall("tenant/recover-password", [ 
        "password"  => $body["password"], 
        "username"  => $body["recoveryEmail"], 
        "token"     => $body["recoveryToken"], 
      ]);

      if (!isset($apiResponse["code"]) || !in_array($apiResponse["code"], [200, 400]))
        return $response->withHeader('Location', '/login')->withStatus(302);

      $userMessageType = ($apiResponse["code"] == 200) ? "success" : "warning";
      $userMessage     = ($apiResponse["code"] == 200) ? $apiResponse["data"]["message"] : $apiResponse["error"]["message"];

      $data = [
        "userMessage"     => $userMessage,
        "userMessageType" => $userMessageType,
        "recoveryEmail"   => $body["recoveryEmail"],
        "recoveryToken"   => $body["recoveryToken"]
      ];

      if ($apiResponse["code"] == 200)
        return $view->render($response, 'recover-password.html', $data);
    }
    else if($method == 'GET') 
    {
      if(empty($params["recoveryEmail"]) || empty($params["recoveryToken"]))
        return $response->withHeader('Location', '/login')->withStatus(302);

      $data = [
        "recoveryEmail" => $params["recoveryEmail"],
        "recoveryToken" => $params["recoveryToken"]
      ];    
    }

    return $view->render($response, 'recover-password.html', $data);
  }

}
