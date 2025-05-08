<?php
namespace Backend\Controller\Tenant;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;
use Backend\Exception\TenantException;
use Backend\Utils\Encryption;
use Firebase\JWT\JWT;
use Backend\Utils\Vars;

class Info extends BaseController
{
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();

    if (empty($data['domain']))
      throw new TenantException('Tenant domain required!', 400);

    $tenant = $this->tenantRepository->getTenantInfo($data["domain"]);
    

    return $this->jsonResponse($response, $tenant, 200);
  }
}