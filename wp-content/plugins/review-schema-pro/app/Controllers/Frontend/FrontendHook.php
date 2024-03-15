<?php
/**
 * Main FrontendHook  Class.
 *
 * The main class that initiates and runs the plugin.
 *
 * @package  review-schema-pro
 *
 * @since    1.0.0
 */

namespace Rtrsp\Controllers\Frontend;

use Rtrs\Helpers\Functions;
use Rtrsp\Traits\SingletonTrait;
/**
 * FrontendHook class
 */
class FrontendHook {
	/**
	 * Singletone.
	 */
	use SingletonTrait;
	/**
	 * Initialize hooks
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rtseo_snippet_others_schema_output', [ &$this, 'schema_output' ], 10, 4 );
	}
	/**
	 * Schema output function
	 *
	 * @param [type] $schemaCat schemaCat.
	 * @param [type] $metaData metaData.
	 * @param [type] $without_script without_script.
	 * @param [type] $schema_obj schema_obj.
	 * @return void
	 */
	public function schema_output( $schemaCat, $metaData, $without_script, $schema_obj ) {
		$helper = new Functions();
		$html   = '';
		switch ( $schemaCat ) {
			case 'tv_series':
				$output             = [];
				$output['@context'] = 'https://schema.org';
				$output['@type']    = 'TVSeries';
				if ( ! empty( $metaData['name'] ) ) {
					$output['name'] = esc_html( $metaData['name'] );
				}
				$author_type = esc_html( $metaData['author-type'] ) ?? 'Person';
				if ( ! empty( $metaData['author'] ) ) {
					$output['author'] = [
						'@type' => $author_type,
						'name'  => esc_html( $metaData['author'] ),
					];
				}

				if ( ! empty( $metaData['actor'] ) ) {
					foreach ( $metaData['actor'] as $value ) {
						if ( ! empty( $value['actor-name'] ) ) {
							$output['actor'][] = [
								'@type' => 'Person',
								'name'  => esc_html( $value['actor-name'] ),
							];
						}
					}
				}
				if ( ! empty( $metaData['description'] ) ) {
					$output['description'] = esc_html( $metaData['description'] );
				}
				if ( ! empty( $metaData['season'] ) ) {
					foreach ( $metaData['season'] as $value ) {
						$tvsession = [
							'@type'            => 'TVSeason',
							'datePublished'    => esc_html( $value['date-published'] ),
							'name'             => esc_html( $value['season-name'] ),
							'numberOfEpisodes' => esc_html( $value['number-of-episodes'] ),
						];
						$episode   = [];
						if ( ! empty( $value['episode-name'] ) ) {
							$episode['name'] = esc_html( $value['episode-name'] );
						}
						if ( ! empty( $value['episode-number'] ) ) {
							$episode['episodeNumber'] = esc_html( $value['episode-number'] );
						}
						if ( ! empty( $episode ) ) {
							$tvsession['episode'] = array_merge(
								[
									'@type' => 'TVEpisode',
								],
								$episode
							);
						}
						$output['containsSeason'][] = $tvsession;
					}
				}
				if ( $without_script ) {
					$html = apply_filters( 'rtseo_snippet_tv_series', $output, $metaData );
				} else {
					$html .= $schema_obj->getJsonEncode( apply_filters( 'rtseo_snippet_tv_series', $output, $metaData ) );
				}
				break;

			case 'collection_page': // This will remove
				$collection_page = [
					'@context' => 'https://schema.org',
					'@type'    => 'CollectionPage',
				];
				if ( ! empty( $metaData['name'] ) ) {
					$collection_page['name'] = $helper->sanitizeOutPut( $metaData['name'] );
				}
				if ( ! empty( $metaData['webpage_url'] ) ) {
					$collection_page['url'] = $helper->sanitizeOutPut( $metaData['webpage_url'], 'url' );
				}
				if ( ! empty( $metaData['description'] ) ) {
					$collection_page['description'] = $helper->sanitizeOutPut( $metaData['description'], 'textarea' );
				}
				if ( ! empty( $metaData['image'] ) ) {
					$img                      = $helper->imageInfo( absint( $metaData['image'] ) );
					$collection_page['image'] = [
						'@type'  => 'ImageObject',
						'url'    => $helper->sanitizeOutPut( $img['url'], 'url' ),
						'height' => $img['height'],
						'width'  => $img['width'],
					];
				}

				if ( ! empty( $metaData['itempage'] ) ) {
					foreach ( $metaData['itempage']  as $key => $has ) {
						$hasPart = [
							'@type'    => 'ItemPage',
							'position' => absint( $key ) + 1,
						];
						if ( ! empty( $has['itempage-name'] ) ) {
							$hasPart['name'] = $helper->sanitizeOutPut( $has['itempage-name'] );
						}
						if ( ! empty( $has['mainEntityOfPage'] ) ) {
							$hasPart['mainEntityOfPage'] = $helper->sanitizeOutPut( $has['mainEntityOfPage'], 'url' );
						}
						if ( ! empty( $has['itempage-description'] ) ) {
							$hasPart['description'] = $helper->sanitizeOutPut( $has['itempage-description'], 'textarea' );
						}
						$collection_page['hasPart'][] = $hasPart;
					}
				}

				if ( $without_script ) {
					$html = apply_filters( 'rtseo_snippet_collection_page', $collection_page, $metaData );
				} else {
					$html .= $schema_obj->getJsonEncode( apply_filters( 'rtseo_snippet_collection_page', $collection_page, $metaData ) );
				}
				break;
			default:
		}
		echo wp_kses(
			$html,
			[
				'script' => [
					'type' => [],
				],
			]
		);
	}

}
