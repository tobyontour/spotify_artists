<?php

namespace Drupal\spotify_artists\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Spotify Artists routes.
 */
class SpotifyArtistsController extends ControllerBase {

  /**
   * Returns a page title.
   */
  public function getTitle(string $artist_id) {
    $artist = $this->getArtist($artist_id);
    if ($artist) {
      return $artist ? $artist['name'] : $this->t('Artist not found');
    }
  }

  /**
   * Builds the response.
   */
  public function build(string $artist_id) {
    $artist = $this->getArtist($artist_id);

    if ($artist) {
      $build['content'] = [
        '#theme' => 'artist_page',
        '#artist' => $artist,
      ];
    }

    return $build;
  }

  protected function getArtist(string $artist_id) {

    if (!isset($this->artists[$artist_id])) {
      $this->artists[$artist_id] = \Drupal::service('spotify_artists.api')->getArtist($artist_id);
    }

    return $this->artists[$artist_id];
  }

}
