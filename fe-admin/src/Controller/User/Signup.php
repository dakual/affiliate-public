<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Utils\Encryption;

class Signup extends BaseController 
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
      $apiResponse = $this->apiCall("tenant/register", [ 
        "username" => $body["username"], 
        "password" => $body["password"], 
        "name"     => $body["name"], 
        "company"  => $body["company"], 
      ]);

      if($apiResponse["code"] == 200) {
        $data = [ 
          "emailVerify" => $apiResponse["data"]["username"], 
          "emailToken"  => $apiResponse["data"]["token"] 
        ];

        return $response->withHeader('Location', '/signup?' . http_build_query($data))->withStatus(302);
      }

      $data = [ "errorMessage" => $apiResponse["error"]["message"] ];
    }
    else if($method == 'GET' && !empty($params["emailVerify"]) && !empty($params["emailToken"])) 
    {
      $token = Encryption::Decode($params["emailToken"], $this->secretKey);
      if(!$token) 
        return $response->withHeader('Location', '/login')->withStatus(302);

      $token = explode('-', $token);
      if($token[0] != $params["emailVerify"]) 
        return $response->withHeader('Location', '/login')->withStatus(302);

      $data = [ 
        "emailVerify" => $params["emailVerify"], 
        "emailToken"  => $params["emailToken"]
      ];

      return $view->render($response, 'verify.html', $data);
    } 

    return $view->render($response, 'signup.html', $data);
  }

}
