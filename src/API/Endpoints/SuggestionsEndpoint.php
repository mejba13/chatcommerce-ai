<?php
/**
 * Suggestions API Endpoint
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\API\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Quick suggestions endpoint.
 */
class SuggestionsEndpoint {
	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'chatcommerce/v1',
			'/suggestions',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_suggestions' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Get quick suggestions.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_suggestions( $request ) {
		$suggestions = array(
			__( 'What products do you have?', 'chatcommerce-ai' ),
			__( 'What are your shipping options?', 'chatcommerce-ai' ),
			__( 'Tell me about returns', 'chatcommerce-ai' ),
			__( 'Do you offer gift wrapping?', 'chatcommerce-ai' ),
			__( 'How can I track my order?', 'chatcommerce-ai' ),
		);

		return new WP_REST_Response(
			array(
				'success'     => true,
				'suggestions' => $suggestions,
			),
			200
		);
	}
}
