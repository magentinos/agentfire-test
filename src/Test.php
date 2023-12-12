<?php

declare( strict_types=1 );

namespace AgentFire\Plugin;

use AgentFire\Plugin\Test\Traits\Singleton;
use AgentFire\Plugin\Test\Rest;
use AgentFire\Plugin\Test\Admin;
use AgentFire\Plugin\Test\Marker;
use AgentFire\Plugin\Test\Shortcode;

/**
 * Class Test
 * @package AgentFire\Plugin\Test
 */
class Test {
	use Singleton;

	public function __construct() {
		Marker::getInstance();
		Rest::getInstance();
		Admin::getInstance();
		Shortcode::getInstance();
	}
}
