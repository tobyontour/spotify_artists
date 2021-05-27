<?php

namespace Drupal\spotify_artists\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Provides a Spotify artist listing block.
 *
 * @Block(
 *   id = "spotify_artists_listing",
 *   admin_label = @Translation("Spotify Artist Listings"),
 *   category = @Translation("Spotify Artists")
 * )
 */
class ArtistBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'number_of_artists' => 5,
      'search_term' => 'a',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['number_of_artists'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of artists to display'),
      '#default_value' => $this->configuration['number_of_artists'],
      '#min' => 1,
      '#max' => 20,
      '#required' => TRUE,
    ];
    $form['number_of_artists'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search term to use'),
      '#default_value' => $this->configuration['search_term'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['number_of_artists'] = $form_state->getValue('number_of_artists');
    $this->configuration['search_term'] = $form_state->getValue('search_term');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $artists = \Drupal::service('spotify_artists.api')->getArtistList(
      $this->configuration['search_term'],
      $this->configuration['number_of_artists']
    );

    $build['content'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => [],
    ];
    foreach ($artists as $id => $artist) {
      $build['content']['#items'][] = Link::createFromRoute($artist, 'spotify_artists.artist', ['artist_id' => $id]);
    }
    return $build;
  }

}
