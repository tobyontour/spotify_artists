<?php

namespace Drupal\spotify_artists\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Spotify Artists routes.
 */
class SpotifyArtistsController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build(string $artist_id) {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Page for ') . $artist_id,
    ];

    return $build;
  }

}
