<?php
/**
 * Status API Endpoint
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\API\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * System status endpoint (admin only).
 */
class StatusEndpoint {
	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'chatcommerce/v1',
			'/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Get system status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_status( $request ) {
		global $wpdb;

		$settings = get_option( 'chatcommerce_ai_settings', array() );

		$status = array(
			'enabled'      => ! empty( $settings['enabled'] ),
			'api_key_set'  => ! empty( $settings['openai_api_key'] ),
			'model'        => $settings['openai_model'] ?? 'gpt-4-turbo-preview',
			'php_version'  => PHP_VERSION,
			'wp_version'   => get_bloginfo( 'version' ),
			'wc_version'   => defined( 'WC_VERSION' ) ? WC_VERSION : null,
			'db_version'   => get_option( 'chatcommerce_ai_db_version' ),
			'stats'        => array(
				'total_sessions' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}chatcommerce_sessions" ),
				'total_messages' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}chatcommerce_messages" ),
				'total_leads'    => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}chatcommerce_leads" ),
				'indexed_docs'   => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}chatcommerce_sync_index" ),
			),
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'status'  => $status,
			),
			200
		);
	}
}
