<?php

declare( strict_types=1 );

namespace AgentFire\Plugin\Test;

use AgentFire\Plugin\Test\Traits\Singleton;

/**
 * Class Shortcode
 * @package AgentFire\Plugin\Test
 */
class Shortcode {
	use Singleton;

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueueCssScripts' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueueJsScripts' ] );
		add_shortcode( 'agentfire_test', [ $this, 'render' ] );
	}

	public function render(): string {
		$context = [
			'is_user_logged_in' => is_user_logged_in(),
			'tags'              => self::getAllTestTags(),
		];

		return Template::getInstance()->render( 'main.twig', $context );
	}

	private static function getAllTestTags(): array {
		$testTagsTerms = get_terms( [
			'taxonomy'   => 'test_tag',
			'hide_empty' => false,
		] );

		return array_map( function ( $term ) {
			return [
				'id'   => $term->term_id,
				'name' => $term->name,
			];
		}, $testTagsTerms );
	}

	public static function enqueueCssScripts() {
		wp_enqueue_style(
			'bootstrap-css',
			AGENTFIRE_TEST_URL . 'bower_components/bootstrap/dist/css/bootstrap.min.css'
		);
		wp_enqueue_style(
			'mapbox-gl-css',
			'https://api.mapbox.com/mapbox-gl-js/v3.0.0/mapbox-gl.css'
		);
		wp_enqueue_style(
			'select2',
			AGENTFIRE_TEST_URL . 'bower_components/select2/dist/css/select2.min.css'
		);
		wp_enqueue_style(
			'main-styles',
			AGENTFIRE_TEST_URL . 'assets/css/styles.css'
		);
	}

	public static function enqueueJsScripts() {
		wp_enqueue_script(
			'jquery',
			AGENTFIRE_TEST_URL . 'bower_components/jquery/dist/jquery.min.js'
		);
		wp_enqueue_script(
			'bootstrap-bundle-js',
			AGENTFIRE_TEST_URL . 'bower_components/bootstrap/dist/js/bootstrap.bundle.min.js',
			[ 'jquery' ]
		);
		wp_enqueue_script(
			'mapbox-gl-js',
			'https://api.mapbox.com/mapbox-gl-js/v3.0.0/mapbox-gl.js'
		);
		wp_enqueue_script(
			'select2',
			AGENTFIRE_TEST_URL . 'bower_components/select2/dist/js/select2.js',
			[ 'jquery' ]
		);
		wp_enqueue_script(
			'main-scripts',
			AGENTFIRE_TEST_URL . 'assets/js/scripts.js',
			[ 'jquery' ]
		);
		wp_localize_script( 'main-scripts', 'initData', self::getInitData() );
	}

	/**
	 * Returns list of params for JS
	 *
	 * @return array
	 */
	private static function getInitData(): array {
		return [
			'mapbox_access_token' => get_field( 'mapbox_access_token', 'option' ),
			'is_user_logged_in'   => is_user_logged_in(),
			'api_endpoint'        => get_site_url() . '/wp-json/' . Rest::NAMESPACE . Rest::REST_BASE,
			'nonce'               => wp_create_nonce( 'wp_rest' ),
		];
	}

}
