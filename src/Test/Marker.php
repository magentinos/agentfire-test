<?php

declare( strict_types=1 );

namespace AgentFire\Plugin\Test;

use AgentFire\Plugin\Test\Traits\Singleton;

/**
 * Class Marker
 * @package AgentFire\Plugin\Test
 */
class Marker {
	use Singleton;

	public $markerKey = 'test_marker';
	public $tagKey = 'test_tag';

	public function __construct() {
		add_action( 'init', [ $this, 'registerMarker' ] );
		add_action( 'init', [ $this, 'registerTag' ] );
	}

	public function registerMarker() {
		register_post_type( $this->markerKey, [
			'labels'       => [
				'name'          => __( 'Markers', AGENTFIRE_TEST_L10N_DOMAIN ),
				'singular_name' => __( 'Marker', AGENTFIRE_TEST_L10N_DOMAIN ),
			],
			'show_in_rest' => true,
		] );
	}

	public function registerTag() {
		register_taxonomy( $this->tagKey, $this->markerKey, [
			'labels'       => [
				'name'          => __( 'Test Tags', AGENTFIRE_TEST_L10N_DOMAIN ),
				'singular_name' => __( 'Test Tag', AGENTFIRE_TEST_L10N_DOMAIN ),
			],
			'public'       => true,
			'show_in_rest' => true,
		] );
	}

}
