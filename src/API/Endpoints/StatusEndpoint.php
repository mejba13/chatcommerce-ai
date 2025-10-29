<?php
/**
 * Status API Endpoint
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\API\Endpoints;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ChatCommerceAI\Admin\AdminController;

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

		register_rest_route(
			'chatcommerce/v1',
			'/test-connection',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'test_connection' ),
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
		$request_id = $this->generate_request_id();

		// Check plugin version.
		$plugin_version = defined( 'CHATCOMMERCE_AI_VERSION' ) ? CHATCOMMERCE_AI_VERSION : 'unknown';

		// Check REST API availability.
		$rest_available = rest_url() ? true : false;

		// Check OpenAI configuration.
		$api_key_set = ! empty( $settings['openai_api_key'] );
		$model = $settings['openai_model'] ?? 'gpt-4o-mini';

		// Get last error from transient.
		$last_error = get_transient( 'chatcommerce_ai_last_error' );

		// Perform OpenAI connectivity probe.
		$openai_connectivity = $this->probe_openai_connectivity();

		$status = array(
			'request_id'   => $request_id,
			'timestamp'    => current_time( 'c' ),
			'plugin'       => array(
				'version'    => $plugin_version,
				'enabled'    => ! empty( $settings['enabled'] ),
				'db_version' => get_option( 'chatcommerce_ai_db_version' ),
			),
			'system'       => array(
				'php_version' => PHP_VERSION,
				'wp_version'  => get_bloginfo( 'version' ),
				'wc_version'  => defined( 'WC_VERSION' ) ? WC_VERSION : null,
				'rest_api'    => $rest_available,
			),
			'openai'       => array(
				'api_key_set'  => $api_key_set,
				'model'        => $model,
				'connectivity' => $openai_connectivity,
			),
			'stats'        => array(
				'total_sessions' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}chatcommerce_sessions" ),
				'total_messages' => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}chatcommerce_messages" ),
				'total_leads'    => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}chatcommerce_leads" ),
				'indexed_docs'   => $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}chatcommerce_sync_index" ),
			),
			'last_error'   => $last_error ? array(
				'code'      => $last_error['code'] ?? 'unknown',
				'message'   => $last_error['message'] ?? 'Unknown error',
				'timestamp' => $last_error['timestamp'] ?? null,
			) : null,
		);

		// Log status check.
		$this->log_diagnostic(
			'status_check',
			$request_id,
			'Status endpoint called',
			array( 'user_id' => get_current_user_id() )
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'status'  => $status,
			),
			200
		);
	}

	/**
	 * Test OpenAI connection.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function test_connection( $request ) {
		$request_id = $this->generate_request_id();
		$start_time = microtime( true );

		// Get settings.
		$settings = get_option( 'chatcommerce_ai_settings', array() );

		// Check if API key is set.
		if ( empty( $settings['openai_api_key'] ) ) {
			$this->log_diagnostic(
				'connection_test',
				$request_id,
				'Connection test failed: API key not set',
				array( 'user_id' => get_current_user_id() )
			);

			return new WP_Error(
				'no_api_key',
				__( 'OpenAI API key is not configured.', 'chatcommerce-ai' ),
				array( 'status' => 400 )
			);
		}

		// Decrypt API key.
		$api_key = AdminController::decrypt_api_key( $settings['openai_api_key'] );
		$model = $settings['openai_model'] ?? 'gpt-4o-mini';

		// Make a lightweight test request to OpenAI.
		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => $model,
						'messages'   => array(
							array(
								'role'    => 'user',
								'content' => 'test',
							),
						),
						'max_tokens' => 5,
					)
				),
				'timeout' => 10,
			)
		);

		$latency = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		// Check for WP_Error.
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();

			// Store error.
			$this->store_error( 'network_error', $error_message, $request_id );

			// Log diagnostic.
			$this->log_diagnostic(
				'connection_test',
				$request_id,
				'Connection test failed: ' . $error_message,
				array(
					'latency_ms' => $latency,
					'user_id'    => get_current_user_id(),
				)
			);

			return new WP_Error(
				'network_error',
				sprintf(
					/* translators: %s: error message */
					__( 'Network error: %s', 'chatcommerce-ai' ),
					$error_message
				),
				array(
					'status'     => 500,
					'request_id' => $request_id,
					'latency_ms' => $latency,
				)
			);
		}

		// Parse response.
		$status_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Map HTTP status codes to user-friendly messages.
		$error_map = array(
			401 => __( 'Invalid API key. Please check your OpenAI API key.', 'chatcommerce-ai' ),
			403 => __( 'Access forbidden. Your API key may not have sufficient permissions.', 'chatcommerce-ai' ),
			429 => __( 'Rate limit exceeded. Please try again later.', 'chatcommerce-ai' ),
			500 => __( 'OpenAI server error. Please try again later.', 'chatcommerce-ai' ),
			502 => __( 'Bad gateway. OpenAI service may be temporarily unavailable.', 'chatcommerce-ai' ),
			503 => __( 'Service unavailable. OpenAI service may be temporarily down.', 'chatcommerce-ai' ),
		);

		if ( $status_code !== 200 ) {
			$error_message = $error_map[ $status_code ] ?? sprintf(
				/* translators: %d: HTTP status code */
				__( 'HTTP error %d', 'chatcommerce-ai' ),
				$status_code
			);

			// Add API error details if available.
			if ( isset( $data['error']['message'] ) ) {
				$error_message .= ': ' . $data['error']['message'];
			}

			// Store error.
			$this->store_error( 'http_' . $status_code, $error_message, $request_id );

			// Log diagnostic.
			$this->log_diagnostic(
				'connection_test',
				$request_id,
				'Connection test failed: ' . $error_message,
				array(
					'status_code' => $status_code,
					'latency_ms'  => $latency,
					'user_id'     => get_current_user_id(),
				)
			);

			return new WP_Error(
				'api_error',
				$error_message,
				array(
					'status'      => $status_code,
					'request_id'  => $request_id,
					'latency_ms'  => $latency,
					'api_error'   => $data['error'] ?? null,
				)
			);
		}

		// Success!
		$this->log_diagnostic(
			'connection_test',
			$request_id,
			'Connection test successful',
			array(
				'latency_ms' => $latency,
				'model'      => $model,
				'user_id'    => get_current_user_id(),
			)
		);

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => __( 'Connection successful!', 'chatcommerce-ai' ),
				'request_id' => $request_id,
				'latency_ms' => $latency,
				'model'      => $model,
				'tokens'     => $data['usage']['total_tokens'] ?? 0,
			),
			200
		);
	}

	/**
	 * Probe OpenAI connectivity (lightweight check).
	 *
	 * @return array
	 */
	private function probe_openai_connectivity() {
		$settings = get_option( 'chatcommerce_ai_settings', array() );

		// Check if API key is set.
		if ( empty( $settings['openai_api_key'] ) ) {
			return array(
				'status'  => 'not_configured',
				'message' => __( 'API key not configured', 'chatcommerce-ai' ),
			);
		}

		// Check cached connectivity status (valid for 5 minutes).
		$cached = get_transient( 'chatcommerce_ai_connectivity_probe' );
		if ( $cached !== false ) {
			return $cached;
		}

		// Perform lightweight connectivity check.
		$api_key = AdminController::decrypt_api_key( $settings['openai_api_key'] );
		$model = $settings['openai_model'] ?? 'gpt-4o-mini';

		$start_time = microtime( true );
		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => $model,
						'messages'   => array(
							array(
								'role'    => 'user',
								'content' => 'ping',
							),
						),
						'max_tokens' => 1,
					)
				),
				'timeout' => 5,
			)
		);
		$latency = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		if ( is_wp_error( $response ) ) {
			$result = array(
				'status'     => 'error',
				'message'    => $response->get_error_message(),
				'latency_ms' => $latency,
			);
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			if ( $status_code === 200 ) {
				$result = array(
					'status'     => 'connected',
					'message'    => __( 'Connected', 'chatcommerce-ai' ),
					'latency_ms' => $latency,
				);
			} else {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				$result = array(
					'status'      => 'error',
					'message'     => $data['error']['message'] ?? sprintf( 'HTTP %d', $status_code ),
					'latency_ms'  => $latency,
					'status_code' => $status_code,
				);
			}
		}

		// Cache result for 5 minutes.
		set_transient( 'chatcommerce_ai_connectivity_probe', $result, 5 * MINUTE_IN_SECONDS );

		return $result;
	}

	/**
	 * Generate request ID.
	 *
	 * @return string
	 */
	private function generate_request_id() {
		return 'req_' . wp_generate_password( 16, false );
	}

	/**
	 * Store error in transient.
	 *
	 * @param string $code Error code.
	 * @param string $message Error message.
	 * @param string $request_id Request ID.
	 */
	private function store_error( $code, $message, $request_id ) {
		set_transient(
			'chatcommerce_ai_last_error',
			array(
				'code'       => $code,
				'message'    => $message,
				'request_id' => $request_id,
				'timestamp'  => current_time( 'c' ),
			),
			DAY_IN_SECONDS
		);

		// Also store in recent errors list (last 5).
		$recent_errors = get_option( 'chatcommerce_ai_recent_errors', array() );
		array_unshift(
			$recent_errors,
			array(
				'code'       => $code,
				'message'    => $message,
				'request_id' => $request_id,
				'timestamp'  => current_time( 'c' ),
			)
		);
		$recent_errors = array_slice( $recent_errors, 0, 5 );
		update_option( 'chatcommerce_ai_recent_errors', $recent_errors );
	}

	/**
	 * Log diagnostic information.
	 *
	 * @param string $event_type Event type.
	 * @param string $request_id Request ID.
	 * @param string $message Log message.
	 * @param array  $context Additional context.
	 */
	private function log_diagnostic( $event_type, $request_id, $message, $context = array() ) {
		// Use WordPress error_log with structured format.
		error_log(
			sprintf(
				'[ChatCommerce AI] [%s] [%s] %s | Context: %s',
				$event_type,
				$request_id,
				$message,
				wp_json_encode( $context )
			)
		);
	}
}
