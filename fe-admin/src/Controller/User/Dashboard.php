<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class Dashboard extends BaseController 
{

  public function __invoke(Request $request, Response $response): Response
  {
    $view   = Twig::fromRequest($request);
    $method = $request->getMethod();
    $body   = (array) $request->getParsedBody();
    $params = $request->getQueryParams();
    $tenant = $request->getAttribute('tenant');
    $data   = [];

    return $view->render($response, 'dashboard.html', $tenant);
  }

}
