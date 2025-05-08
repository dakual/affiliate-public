<?php
namespace Backend\Handler;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Interfaces\CallableResolverInterface;
use Fig\Http\Message\StatusCodeInterface;
use Slim\Logger;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface 
{
  protected CallableResolverInterface $callableResolver;
  protected ResponseFactoryInterface $responseFactory;
  protected LoggerInterface $logger;

  public function __construct(
    CallableResolverInterface $callableResolver,
    ResponseFactoryInterface $responseFactory,
    ?LoggerInterface $logger = null
  ) {
    $this->callableResolver = $callableResolver;
    $this->responseFactory = $responseFactory;
    $this->logger = $logger ?: new Logger();
  }

  public function __invoke(
    Request $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
  ): Response {
    $statusCode = $exception->getCode();
    $className  = new \ReflectionClass(get_class($exception));

    if ($logErrors) {
      $error = $this->getErrorDetails($exception, $logErrorDetails);
      $error['method'] = $request->getMethod();
      $error['url'] = (string) $request->getUri();

      $this->logger->error($exception->getMessage(), $error);
    }
  
    $response = $this->responseFactory->createResponse();
    $response->getBody()->write(
      json_encode([
        'code'  => $statusCode,
        'error' => $this->getErrorDetails($exception, $displayErrorDetails),
      ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );

    return $response
      ->withStatus(200)
      ->withHeader('Content-type', 'application/problem+json'); //$this->getHttpStatusCode($exception)
  }

  private function getHttpStatusCode(Throwable $exception): int
  {
    $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    if ($exception instanceof HttpException) {
      $statusCode = (int)$exception->getCode();
    }

    if ($exception instanceof DomainException || $exception instanceof InvalidArgumentException) {
      $statusCode = StatusCodeInterface::STATUS_BAD_REQUEST;
    }

    $file = basename($exception->getFile());
    if ($file === 'CallableResolver.php') {
      $statusCode = StatusCodeInterface::STATUS_NOT_FOUND;
    }

    return $statusCode;
  }

  private function getErrorDetails(Throwable $exception, bool $displayErrorDetails): array
  {
    if ($displayErrorDetails === true) {
      return [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        // 'previous' => $exception->getPrevious(),
        // 'trace' => $exception->getTrace(),
      ];
    }

    return [
      'message' => $exception->getMessage(),
    ];
  }
}