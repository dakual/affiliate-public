<?php
use Selective\BasePath\BasePathMiddleware;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Controller\User\Register;
use App\Controller\User\Login;
use App\Controller\User\Verify;
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
  $group->map(['GET', 'POST'], '/{domain}/register', Register::class)->setName('register');
  $group->map(['GET', 'POST'], '/{domain}/verify', Verify::class)->setName('verify');
  $group->map(['GET', 'POST'], '/{domain}/login', Login::class)->setName('login');
})->add(new App\Middleware\Auth('dashboard'));

$app->group('', function (RouteCollectorProxy $group) {
  $group->map(['GET', 'POST'], '/{domain}/dashboard', Dashboard::class)->setName('dashboard');
})->add(new App\Middleware\Auth('login'));


$app->run();