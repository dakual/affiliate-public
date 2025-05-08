<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Utils\Encryption;

class Register extends BaseController 
{

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $view   = Twig::fromRequest($request);
    $method = $request->getMethod();
    $body   = (array) $request->getParsedBody();
    $params = $request->getQueryParams();
    $data   = [];

    $apiResponse = $this->apiCall("tenant/info", [ 
      "domain" => $args["domain"]
    ]);
    
    if($apiResponse["code"] != 200) 
      return $response->withHeader('Location', '/')->withStatus(302);        

    if($method == 'POST') 
    {
      $apiResponse = $this->apiCall("user/register", [ 
        "username" => $body["username"], 
        "password" => $body["password"], 
        "name"     => $body["name"] 
      ]);

      if($apiResponse["code"] == 200) {
        $data = [ 
          "emailVerify" => $apiResponse["data"]["username"], 
          "emailToken"  => $apiResponse["data"]["token"] 
        ];

        $url = '/' . $args["domain"] . '/register?' . http_build_query($data);
        return $response->withHeader('Location', $url)->withStatus(302);
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

      $data  = [ 
        "emailVerify" => $params["emailVerify"], 
        "emailToken"  => $params["emailToken"],
        "resendHash"  => hash('sha256', $params["emailVerify"])
      ];

      return $view->render($response, 'verify.html', $data);
    } 

    return $view->render($response, 'register.html', $data);
  }

}