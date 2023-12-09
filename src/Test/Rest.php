<?php

declare( strict_types=1 );

namespace AgentFire\Plugin\Test;

use AgentFire\Plugin\Test\Traits\Singleton;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Rest
 * @package AgentFire\Plugin\Test
 */
class Rest {
	use Singleton;

	/**
	 * @var string Endpoint namespace
	 */
	const NAMESPACE = 'agentfire/v1/';

	/**
	 * @var string Route base
	 */
	const REST_BASE = 'test';

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
	}

	/**
	 * Register endpoints
	 */
	public static function registerRoutes() {
		register_rest_route( self::NAMESPACE, self::REST_BASE . '/markers', [
			'show_in_index' => false,
			'methods'       => [ WP_REST_Server::READABLE, WP_REST_Server::CREATABLE ],
			'callback'      => [ self::class, 'markers' ],
			'args'          => [],

		] );

        register_rest_route( self::NAMESPACE, self::REST_BASE . '/tags', [
            'show_in_index' => false,
            'methods'       => [ WP_REST_Server::READABLE ],
            'callback'      => [ self::class, 'tags' ],
            'args'          => [],

        ] );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function markers( WP_REST_Request $request ): WP_REST_Response {

        $method = $request->get_method();

        if ( $method == WP_REST_Server::READABLE ) {
            $filters = $request->get_query_params();

            $markers_data = [
                'post_type'      => 'test_marker',
                'posts_per_page' => -1,
            ];

            if ( !empty($filters['search']) ) {
                $markers_data['s'] = $filters['search'];
            }

            if (!empty($filters['currentUserMarkers'])) {
                $markers_data['author'] = get_current_user_id();
            }

            if ( !empty($filters['tags']) ) {
                $markers_data['tax_query'] = [
                    [
                        'taxonomy'         => 'test_tag',
                        'field'            => 'term_id',
                        'terms'            => $filters['tags'],
                        'include_children' => true,
                        'operator'         => 'IN',
                    ],
                ];
            }

            $markers = get_posts($markers_data);

            $results = [];

            if ( !empty($markers) ) {
                foreach ($markers as $marker) {
                    $meta = get_post_meta($marker->ID); // coordinates

                    if (empty($meta['lat'][0]) || empty($meta['lng'][0])) {
                        continue;
                    }

                    $item = [
                        'id'       => $marker->ID,
                        'name'     => $marker->post_title,
                        'lat'      => $meta['lat'][0],
                        'lng'      => $meta['lng'][0],
                        'date'     => $marker->post_date,
                        'is_owner' => $marker->post_author == get_current_user_id(),
                        'author'   => $marker->post_author,
                        'tags'     => '',
                    ];

                    $tags = get_the_terms($marker->ID, 'test_tag');
                    if (!empty( $tags )) {
                        $tag_names = array_map( function ($tag) {
                            return $tag->name;
                        }, $tags );

                        $item['tags'] = implode(', ', $tag_names);
                    }

                    $results[] = $item;
                }
            }

            return new WP_REST_Response( $results );
        } elseif ( $method == WP_REST_Server::CREATABLE ) {
            if (!is_user_logged_in()) {
                return new WP_REST_Response( [ 'success' => false ] );
            }

            $data = $request->get_body_params();

            $insert_data = [
                'post_type'   => 'test_marker',
                'post_status' => 'publish',
                'post_title'  => $data['name'],
                'post_author' => get_current_user_id(),
                'meta_input'  => [
                    'lat' => $data['lat'],
                    'lng' => $data['lng']
                ]
            ];

            $marker_id = wp_insert_post($insert_data);

            $is_new_tag_added = false;
            if ( !empty( $data['tags'] ) ) {

                // TODO can't add tag which name is number
                $tags = array_map( function ( $tag ) use ( &$is_new_tag_added ) {
                    // guessing whether tag is new or already added
                    if (ctype_digit( $tag )) {
                        return ( int ) $tag; // already added
                    } else {
                        $is_new_tag_added = true;
                        return $tag; // new one
                    }
                    return ctype_digit( $tag ) ? ( int ) $tag : $tag ;
                }, $data['tags'] );

                wp_set_object_terms( $marker_id, $tags, 'test_tag' );
            }

            return new WP_REST_Response( [ 'success' => true, 'is_new_tag_added' => $is_new_tag_added ] );
        }
	}

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public static function tags( WP_REST_Request $request ): WP_REST_Response {
        $method = $request->get_method();

        if ( $method == WP_REST_Server::READABLE ) {
            $test_tags_terms = self::getTestTags();

            return new WP_REST_Response( [ 'results' => array_map( function ($term) {
                return [
                    'id'   => $term->term_id,
                    'text' => $term->name,
                ];
            }, $test_tags_terms ) ] );
        }

    }

    private static function getTestTags(): array {
        return get_terms( [
            'taxonomy'   => 'test_tag',
            'hide_empty' => false,
        ] );
    }

}
