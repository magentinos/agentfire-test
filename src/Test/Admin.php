<?php

declare( strict_types=1 );

namespace AgentFire\Plugin\Test;

use AgentFire\Plugin\Test\Traits\Singleton;

/**
 * Class Admin
 * @package AgentFire\Plugin\Test
 */
class Admin {
	use Singleton;

	public $slug = 'test-settings';
	public $key = 'test_settings';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'acf/init', [ $this, 'init' ] );
	}

	/**
	 * Add settings page
	 */
	public function menu() {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page( [
				'page_title' => __( 'AgentFire Test', AGENTFIRE_TEST_L10N_DOMAIN ),
				'menu_title' => __( 'AgentFire Test', AGENTFIRE_TEST_L10N_DOMAIN ),
				'menu_slug'  => 'test-settings',
				'capability' => 'manage_options',
			] );
		} else {
			$slug = add_menu_page( __( 'AgentFire Test', AGENTFIRE_TEST_L10N_DOMAIN ), __( 'AgentFire Test', AGENTFIRE_TEST_L10N_DOMAIN ), 'manage_options', 'test-settings', [
				$this,
				'renderPage'
			] );
			add_action( "load-{$slug}", [ $this, 'adminLoad' ] );
		}
	}

	public function renderPage() {
		$fields      = acf_get_fields( $this->key );
		$field_group = acf_get_field_group( $this->key );
		$options     = [
			'id'         => 'acf-group_' . $this->key,
			'key'        => $field_group['key'],
			'style'      => $field_group['style'],
			'label'      => $field_group['label_placement'],
			'visibility' => true,
		];
		?>
        <div class="wrap acf-settings-wrap">
            <h1>AgentFire Test</h1>
            <form id="post" method="post" name="post">
				<?php
				acf_form_data( [
					'post_id' => 'options',
					'nonce'   => 'options',
				] );
				?>
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="postbox-container-1" class="postbox-container">
                            <div id="side-sortables" class="meta-box-sortables ui-sortable">
                                <div id="submitdiv" class="postbox ">
                                    <button type="button" class="handlediv" aria-expanded="true"><span
                                                class="toggle-indicator" aria-hidden="true"></span></button>
                                    <h2 class="hndle ui-sortable-handle"><span>Save Settings</span></h2>
                                    <div class="inside">
                                        <div id="major-publishing-actions">
                                            <div id="publishing-action">
                                                <span class="spinner"></span>
                                                <input type="submit" accesskey="p" value="Save Settings"
                                                       class="button button-primary button-large" id="publish"
                                                       name="publish">
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="postbox-container-2" class="postbox-container">
                            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                                <div id="<?= $options['id'] ?>" class="postbox acf-postbox">
                                    <button type="button" class="handlediv" aria-expanded="true"><span
                                                class="toggle-indicator" aria-hidden="true"></span></button>
                                    <h2 class="hndle ui-sortable-handle"><span>Settings</span></h2>
                                    <div class="inside acf-fields -top">
										<?php
										acf_render_fields( $fields, 'options' );
										?>
                                        <script type="text/javascript">
                                            if (typeof acf !== 'undefined') {
                                                var postboxOptions = <?php echo json_encode( $options ); ?>;
                                                if (typeof acf.newPostbox === 'function') {
                                                    acf.newPostbox(postboxOptions);
                                                } else if (typeof acf.postbox.render === 'function') {
                                                    acf.postbox.render(postboxOptions);
                                                }
                                            }
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br class="clear">
                    </div>
                </div>
            </form>
        </div>
		<?php
	}

	public function adminLoad() {
		if ( acf_verify_nonce( 'post' ) ) {
			if ( acf_validate_save_post( true ) ) {
				acf_save_post( 'options' );
				wp_redirect( add_query_arg( [ 'message' => '1' ] ) );
				exit;
			}
		}
		acf_enqueue_scripts();
		wp_enqueue_script( 'post' );
	}

	/**
	 * Set settings page fields
	 */
	public function init() {
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			acf_add_local_field_group( [
				'key'                   => $this->key,
				'title'                 => __( 'Test Settings', AGENTFIRE_TEST_L10N_DOMAIN ),
				'fields'                => [
					[
						'key'      => 'mapbox_access_token',
						'label'    => __( 'Mapbox Access Token', AGENTFIRE_TEST_L10N_DOMAIN ),
						'name'     => 'mapbox_access_token',
						'type'     => 'text',
						'required' => 1,
					],
				],
				'location'              => [
					[
						[
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => $this->slug,
						],
					],
				],
				'menu_order'            => 10,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => 1,
				'description'           => '',
			] );
		}
	}

}
