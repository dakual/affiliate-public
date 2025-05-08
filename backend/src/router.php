<?php
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Backend\Handler\ErrorHandler;
use Backend\Controller;
use Backend\Controller\Tenant\Login;
use Backend\Controller\Tenant\Logout;
use Backend\Controller\Tenant\Register;
use Backend\Controller\Tenant\RegisterResend;
use Backend\Controller\Tenant\ForgotPassword;
use Backend\Controller\Tenant\RecoverPassword;
use Backend\Controller\Tenant\User;
use Backend\Controller\Tenant\VerifyEmail;
use Backend\Controller\Tenant\Info;


require __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler    = new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory());
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
    ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Credentials', 'true')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->group('/api', function (RouteCollectorProxy $group) 
{
  $group->get('/status', 'Backend\Controller\DefaultController:getStatus');

  $group->group('/tenant', function (RouteCollectorProxy $group) 
  {
    $group->post('/info', Info::class);
    $group->post('/login', Login::class);
    $group->post('/logout', Logout::class);
    $group->post('/register', Register::class);
    $group->post('/register-resend', RegisterResend::class);
    $group->post('/forgot-password', ForgotPassword::class);
    $group->post('/recover-password', RecoverPassword::class);
    $group->post('/verify-email', VerifyEmail::class);

    $group->group('/area', function (RouteCollectorProxy $group) 
    {
      $group->post('', User::class);
    })->add(new Backend\Middleware\Auth());
  });

  $group->group('/user', function (RouteCollectorProxy $group) 
  {
    $group->post('/register', Backend\Controller\User\Register::class);
    $group->post('/verify-email', Backend\Controller\User\VerifyEmail::class);
    $group->post('/login', Backend\Controller\User\Login::class);
  });

});

return $app;