<?php

/**
 * AgentFire Test
 *
 * @link https://agentfire.com
 * @since 1.0.0
 * @package AgentFire\Plugin\Test
 *
 * @wordpress-plugin
 * Plugin Name: AgentFire Test
 * Description: Plugin that draws markers on map
 * Plugin URI: https://agentfire.com
 * Version: 1.0.0
 * Author: Roman Chaikovskyi
 * License: Proprietary
 * Network: false
 */

namespace AgentFire\Plugin;

define( 'AGENTFIRE_TEST_VERSION', '1.0.0' );
define( 'AGENTFIRE_TEST_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGENTFIRE_TEST_L10N_DOMAIN', 'agentfire_test' );
define( 'AGENTFIRE_TEST_URL', plugin_dir_url( __FILE__ ) );

require AGENTFIRE_TEST_PATH . 'vendor/autoload.php';

Test::getInstance();
