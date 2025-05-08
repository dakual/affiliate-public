<?php
namespace App\Middleware;

use App\Utils\HttpClient;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class Auth
{
  public $stage;

  public function __construct(string $stage)
  {
    $this->stage = $stage;
  }

  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $apiResponse = HttpClient::call("tenant/area", []);
    $request     = $request->withAttribute("tenant", $apiResponse);
    $response    = $handler->handle($request);

    if($this->stage == 'dashboard' && @$apiResponse["code"] == 200)
    {
      return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
    
    if($this->stage == 'login' && @$apiResponse["code"] != 200) 
    {
      return $response->withHeader('Location', '/login')->withStatus(302);
    }

    return $response;
  }

}
