<?php
namespace App\Middleware;

use App\Utils\HttpClient;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpNotFoundException;

class Auth
{
  public $stage;

  public function __construct(string $stage)
  {
    $this->stage = $stage;
  }

  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $routeContext = RouteContext::fromRequest($request);
    $route        = $routeContext->getRoute();

    if (empty($route)) {
      throw new HttpNotFoundException($request);
    }

    // $name       = $route->getName();
    // $groups     = $route->getGroups();
    // $methods    = $route->getMethods();
    $arguments  = $route->getArguments();
    
    $apiResponse = HttpClient::call("user/area", [
      "domain" => $arguments["domain"]
    ]);

    $request     = $request->withAttribute("user", $apiResponse);
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
