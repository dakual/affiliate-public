<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;


class User extends BaseController
{
  
  public function __invoke(Request $request, Response $response): Response
  {
    $userId = $this->getUserId($request);
    $data   = $this->tenantRepository->getTenantByHash($userId);


    return $this->jsonResponse($response, $data, 200);
  }

}