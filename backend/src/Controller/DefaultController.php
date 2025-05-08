<?php
namespace Backend\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Controller\BaseController;


class DefaultController extends BaseController
{
    public function getStatus(Request $request, Response $response): Response
    {
      $status = [
        'message'   => 'Backend Api v1.0',
        'version'   => \Composer\InstalledVersions::getVersion('affiliate/backend'),
        'status'    => 'healthy',
        'timestamp' => time()
      ];

      return $this->jsonResponse($response, $status, 200);
    }
}