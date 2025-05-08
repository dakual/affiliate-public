<?php
namespace App\Utils;

use GuzzleHttp\Client;

class HttpClient 
{
  public static function call(string $path, array $data): array 
  {
    $BE_HOST    = getenv('BE_HOST');
    $BE_PORT    = getenv('BE_PORT');
    $BE_VERSION = getenv('BE_VERSION');

    $client = new Client([
      'base_uri' => "http://{$BE_HOST}:{$BE_PORT}/{$BE_VERSION}/",
      'timeout'  => 2.0,
    ]);

    $token = @$_COOKIE['_token'];
    $apiResponse = $client->request('POST', $path, [
      'form_params' => $data,
      'headers'     => [
          'User-Agent'    => 'affiliate/1.0',
          'Authorization' => 'Bearer ' . $token,
          'Accept'        => 'application/json',
      ]
    ]);

    $apiStatus = $apiResponse->getStatusCode();
    $apiBody   = $apiResponse->getBody();

    return json_decode($apiBody, true);
  }
}