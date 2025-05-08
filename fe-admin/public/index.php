<?php
use Selective\BasePath\BasePathMiddleware;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Controller\User\Signup;
use App\Controller\User\Verify;
use App\Controller\User\Login;
use App\Controller\User\ForgotPassword;
use App\Controller\User\RecoverPassword;
use App\Controller\User\Dashboard;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->add(new BasePathMiddleware($app));

$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
    ->withHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0')
    ->withHeader('Pragma', 'no-cache')
    ->withHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
});

$app->get('/', function ($request, $response, $args) {
  $view = Twig::fromRequest($request);
  return $view->render($response, 'index.html', [
      'name' => ''
  ]);
})->setName('home');


$app->group('', function (RouteCollectorProxy $group) {
  $group->map(['GET', 'POST'], '/signup', Signup::class)->setName('signup');
  $group->map(['GET', 'POST'], '/login', Login::class)->setName('login');
  $group->map(['GET', 'POST'], '/forgot-password', ForgotPassword::class)->setName('forgot-password');
  $group->map(['GET', 'POST'], '/recover-password', RecoverPassword::class)->setName('recover-password');
  $group->map(['GET'], '/verify', Verify::class)->setName('verify');
})->add(new App\Middleware\Auth('dashboard'));

$app->group('', function (RouteCollectorProxy $group) {
  $group->map(['GET', 'POST'], '/dashboard', Dashboard::class)->setName('dashboard');
})->add(new App\Middleware\Auth('login'));


$app->run();