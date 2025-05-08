<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;


class Logout extends BaseController
{
  
  public function __invoke(Request $request, Response $response): Response
  {
    $data = array(
      'message' => 'Logout Successfull'
    );

    return $this->jsonResponse($response, $data, 200);
  }

}