<?php
namespace Backend\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Cookies;
use Backend\Repository\TenantRepository;
use Backend\Repository\UserRepository;
use Backend\Utils\MQInterface;
use Backend\Utils\Rabbitmq;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Backend\Utils\Encryption;
use Backend\Utils\Constants;
use Backend\Entity\TenantEntity;
use Backend\Entity\UserEntity;


abstract class BaseController
{
  public TenantRepository $tenantRepository;
  public UserRepository $userRepository;
  public MQInterface $messageQueue;
  public Logger $logger;
  public $jwtPrivateKey;
  public $jwtLifeTime;
  public $jwtIssuer;
  public $secretKey;
  public $siteURL;

  public function __construct()
  {
    $this->tenantRepository = new TenantRepository();
    $this->userRepository   = new UserRepository();
    $this->messageQueue     = new Rabbitmq();
    $this->logger           = new Logger('default');

    $streamHandler = new StreamHandler("php://stdout", Level::Debug);
    $output        = "%datetime% [%level_name%] %message% | %context% %extra%\n";
    $dateFormat    = "Y-m-d H:i:s,u";

    $formatter = new LineFormatter(
        $output, // Format of message in log
        $dateFormat, // Datetime format
        true, // allowInlineLineBreaks option, default false
        true  // discard empty Square brackets in the end, default false
    );
    $streamHandler->setFormatter($formatter);
    $this->logger->pushHandler($streamHandler);

    //$this->logger->pushHandler(new StreamHandler("php://stdout", Level::Info));

    $this->jwtPrivateKey    = getenv('JWT_PRIVATE_KEY');
    $this->jwtLifeTime      = getenv('JWT_LIFETIME');
    $this->jwtIssuer        = getenv('JWT_ISSUER');
    $this->secretKey        = getenv('SECRET_KEY');
    $this->adminURL         = getenv('ADMIN_URL');
    $this->userURL          = getenv('USER_URL');
  }

  protected function jsonResponse(Response $response, $message, int $code): Response 
  {
    $result = [
        'code' => $code,
        'data' => $message
    ];

    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($code);
  }

  protected function getToken(string $username): string 
  {
    return Encryption::Encode($username.'-'.time(), $this->secretKey);
  }

  protected function getUserId(Request $request): string 
  {
    return $request->getAttribute('jwt')->sub;
  }



}

