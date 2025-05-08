<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class Login extends BaseController 
{

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $view   = Twig::fromRequest($request);
    $method = $request->getMethod();
    $body   = (array) $request->getParsedBody();
    $params = $request->getQueryParams();
    $data   = [];

    
    // $apiResponse = $this->apiCall("tenant/info", [ 
    //   "domain" => $args["domain"]
    // ]);

    // if($apiResponse["code"] != 200) 
    //   return $response->withHeader('Location', '/')->withStatus(302);

    if($method == 'POST') 
    {
      $apiResponse = $this->apiCall("user/login", [ 
        "username" => $body["username"], 
        "password" => $body["password"], 
        "remember" => isset($body["remember"]) ? "on" : "off"
      ]);

      if($apiResponse["code"] == 200) {
        $response = $this->setJwtCookie($request, $response, $apiResponse["data"]);
        return $response->withHeader('Location', '/dashboard')->withStatus(302);        
      }

      $data = [
        "userMessage" => $apiResponse["error"]["message"]
      ];
    }
    else if($method == 'GET' && isset($params["verifyEmail"]) && isset($params["verifyToken"])) 
    { 
      $apiResponse = $this->apiCall("user/verify-email", [ 
        "verifyEmail" => $params["verifyEmail"],
        "verifyToken" => $params["verifyToken"]
      ]);

      if($apiResponse["code"] != 200)
        return $response->withHeader('Location', '/login')->withStatus(302);

      $userMessageType = ($apiResponse["code"] != 200) ? "warning" : "success";

      $data = [ 
        "userMessage"     => $apiResponse["data"]["message"],
        "userMessageType" => $userMessageType
      ];
    } 

    return $view->render($response, 'login.html', $data);
  }
}