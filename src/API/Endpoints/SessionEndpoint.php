<?php
/**
 * Session API Endpoint
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\API\Endpoints;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Session management endpoint.
 */
class SessionEndpoint {
	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'chatcommerce/v1',
			'/session/start',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'start_session' ),
				'permission_callback' => '__return_true', // Public endpoint.
			)
		);
	}

	/**
	 * Start a new chat session.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function start_session( $request ) {
		global $wpdb;

		// Generate session ID.
		$session_id = wp_generate_uuid4();

		// Get client info.
		$ip_address = $this->get_client_ip();
		$user_agent = $request->get_header( 'User-Agent' );
		$user_id    = get_current_user_id();

		// Insert session.
		$sessions_table = $wpdb->prefix . 'chatcommerce_sessions';
		$inserted       = $wpdb->insert(
			$sessions_table,
			array(
				'session_id'    => $session_id,
				'user_id'       => $user_id ?: null,
				'ip_address'    => $ip_address,
				'user_agent'    => $user_agent,
				'started_at'    => current_time( 'mysql' ),
				'last_activity' => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return new WP_Error(
				'session_creation_failed',
				__( 'Failed to create session.', 'chatcommerce-ai' ),
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'session_id' => $session_id,
				'settings'   => $this->get_client_settings(),
			),
			200
		);
	}

	/**
	 * Get client IP address.
	 *
	 * @return string|null
	 */
	private function get_client_ip() {
		$settings = get_option( 'chatcommerce_ai_settings', array() );

		if ( empty( $settings['store_ip_address'] ) ) {
			return null;
		}

		// Try various headers for proxies.
		$headers = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_X_FORWARDED_FOR',  // Proxy.
			'HTTP_X_REAL_IP',        // Nginx.
			'REMOTE_ADDR',           // Direct.
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( $_SERVER[ $header ] );
				// Anonymize for privacy.
				return $this->anonymize_ip( $ip );
			}
		}

		return null;
	}

	/**
	 * Anonymize IP address.
	 *
	 * @param string $ip IP address.
	 * @return string
	 */
	private function anonymize_ip( $ip ) {
		// Remove last octet for IPv4.
		if ( strpos( $ip, '.' ) !== false ) {
			$parts       = explode( '.', $ip );
			$parts[ count( $parts ) - 1 ] = '0';
			return implode( '.', $parts );
		}

		// Mask last 80 bits for IPv6.
		if ( strpos( $ip, ':' ) !== false ) {
			return substr( $ip, 0, strrpos( $ip, ':' ) ) . ':0000:0000:0000:0000';
		}

		return $ip;
	}

	/**
	 * Get client-safe settings.
	 *
	 * @return array
	 */
	private function get_client_settings() {
		$settings = get_option( 'chatcommerce_ai_settings', array() );

		return array(
			'welcome_message' => $settings['welcome_message'] ?? __( 'Hi! How can I help you today?', 'chatcommerce-ai' ),
			'suggestions'     => array(
				__( 'What products do you have?', 'chatcommerce-ai' ),
				__( 'What are your shipping options?', 'chatcommerce-ai' ),
				__( 'Tell me about returns', 'chatcommerce-ai' ),
			),
		);
	}
}
