<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ForgotPassword extends BaseController 
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
      $apiResponse = $this->apiCall("tenant/forgot-password", [ 
        "username" => $body["username"]
      ]);

      $data = [
        "userMessage"     => (isset($apiResponse["error"])) ? $apiResponse["error"]["message"] : $apiResponse["data"]["message"],
        "userMessageType" => (isset($apiResponse["error"])) ? "warning" : "success",
      ];
    }

    return $view->render($response, 'forgot-password.html', $data);
  }

}
