<?php

namespace Drupal\spotify_artists\Services;

use Drupal\Core\Cache\CacheFactoryInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;

/**
 * SpotifyApi service.
 */
class SpotifyApi {

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The cache.backend.database service.
   *
   * @var \Drupal\Core\Cache\CacheFactoryInterface
   */
  protected $cacheBackendDatabase;

  /**
   * Constructs a SpotifyApi object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Cache\CacheFactoryInterface $cache_backend_database
   *   The cache.backend.database service.
   */
  public function __construct(LoggerInterface $logger, CacheFactoryInterface $cache_backend_database) {
    $this->logger = $logger;
    $this->cacheBackendDatabase = $cache_backend_database;
  }

  protected function getAuthorizationHeader() {
    $config = \Drupal::config('spotify_artists.settings');
    $client_id = $config->get('client_id');
    $secret_key = $config->get('secret_key');

    if (empty($client_id) || empty($secret_key)) {
      $this->logger->critical('Spotify credentials are empty');
      return NULL;
    }

    return [
      'Authorization' => 'Basic ' . base64_encode("$client_id:$secret_key"),
    ];
  }

  /**
   * Get Spotify Access Token.
   *
   * @return string
   *   Access token or NULL.
   */
  public function getAccessToken() {
    $cid = 'spotify_artists:access_token';
    $data = NULL;
    if ($cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {

      $client = new Client();
      $authorization_header = $this->getAuthorizationHeader();

      if (empty($authorization_header)) {
        return NULL;
      }

      $endpoint = 'https://accounts.spotify.com/api/token';

      try {
        $response = $client->request('POST', $endpoint, [
          'headers' => $authorization_header,
          'form_params' => [
            'grant_type' => 'client_credentials',
          ],
          ['debug' => true]
        ]);
      }
      catch (\Exception $e) {
        $this->logger->error("Spotify token request failed: " . $e->getMessage());
        return NULL;
      }

      $statusCode = $response->getStatusCode();

      if ($statusCode != 200) {
        $this->logger->error("Spotify token fetch returned: " . $response->getBody());
        return NULL;
      }

      $data = json_decode($response->getBody(), TRUE);

      if ($data && !empty($data['access_token'])) {
        \Drupal::state()->set('spotify_auth',$data);
        $cache = \Drupal::cache()->set($cid, $data, $data['expires_in'] + time() - 5);
      }
      else {
        return NULL;
      }
    }
    return $data['access_token'];
  }

  /**
   * GET call to Spotify API.
   *
   * @param string $endpoint
   *   Endpoint to call.
   * @param array $parameters
   *   Query parameters.
   *
   * @return array
   *   Decoded body. Empty on failure.
   */
  protected function get(string $endpoint, array $parameters) {
    $access_token = $this->getAccessToken();
    $data = NULL;

    if ($access_token) {
      $client = new Client();

      try {
        $response = $client->request('GET', $endpoint, [
          'query' => $parameters,
          'headers' => [
            'Authorization' => "Bearer $access_token",
          ],
        ]);
      }
      catch (\Exception $e) {
        $this->logger->error("Call to $endpoint returned: " . $e->getMessage());
      }
      if (isset($response)) {
        $statusCode = $response->getStatusCode();

        if ($statusCode != 200) {
          $this->logger->error("Call to $endpoint returned ($statusCode): " . $response->getBody());
        }
        else {
          $data = json_decode($response->getBody(), TRUE);
        }
      }
    }

    return $data ?? [];
  }

  /**
   * Get Artist List.
   *
   * @param string $searchTerm
   *   Search term. Defaults to 'a' to get artists.
   *
   * @param int $count
   *   Number of artists. Default 20.
   *
   * @return array
   *   Array of artists. Key: ID, Value: name.
   */
  public function getArtistList(string $searchTerm = 'a', int $count = 20) {
    $endpoint = 'https://api.spotify.com/v1/search';

    $data = $this->get($endpoint, [
      'type' => 'artist',
      'q' => 'a',
      'limit' => $count,
    ]);
    if (!empty($data['artists']['items'])) {
      foreach ($data['artists']['items'] as $artist) {
        $data[$artist['id']] = $artist['name'];
      }
    }

    return $data;
  }

}
