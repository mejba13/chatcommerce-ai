<?php
/**
 * Chat API Endpoint
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\API\Endpoints;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ChatCommerceAI\AI\OpenAIClient;
use ChatCommerceAI\Security\RateLimiter;

/**
 * Chat endpoint with SSE streaming.
 */
class ChatEndpoint {
	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'chatcommerce/v1',
			'/chat/stream',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'stream_chat' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'session_id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'message'    => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);
	}

	/**
	 * Stream chat response.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function stream_chat( $request ) {
		$session_id = $request->get_param( 'session_id' );
		$message    = $request->get_param( 'message' );

		// Validate session.
		if ( ! $this->validate_session( $session_id ) ) {
			return new WP_Error(
				'invalid_session',
				__( 'Invalid session ID.', 'chatcommerce-ai' ),
				array( 'status' => 400 )
			);
		}

		// Check rate limit.
		$rate_limiter = new RateLimiter();
		if ( ! $rate_limiter->check( $session_id ) ) {
			return new WP_Error(
				'rate_limit_exceeded',
				__( 'Too many requests. Please try again later.', 'chatcommerce-ai' ),
				array( 'status' => 429 )
			);
		}

		// Store user message.
		$this->store_message( $session_id, 'user', $message );

		// Update session activity.
		$this->update_session_activity( $session_id );

		// Get chat history.
		$history = $this->get_chat_history( $session_id, 6 );

		// Initialize OpenAI client.
		$openai = new OpenAIClient();

		// Check if streaming is supported.
		$accept_header = $request->get_header( 'Accept' );
		$supports_sse  = strpos( $accept_header, 'text/event-stream' ) !== false;

		if ( $supports_sse ) {
			// SSE streaming.
			$this->stream_response( $openai, $session_id, $history, $message );
		} else {
			// Fallback to regular response.
			return $this->regular_response( $openai, $session_id, $history, $message );
		}
	}

	/**
	 * Stream response using SSE.
	 *
	 * @param OpenAIClient $openai OpenAI client.
	 * @param string       $session_id Session ID.
	 * @param array        $history Chat history.
	 * @param string       $message User message.
	 */
	private function stream_response( $openai, $session_id, $history, $message ) {
		// Set SSE headers.
		header( 'Content-Type: text/event-stream' );
		header( 'Cache-Control: no-cache' );
		header( 'Connection: keep-alive' );
		header( 'X-Accel-Buffering: no' ); // Disable nginx buffering.

		// Disable output buffering.
		if ( ob_get_level() ) {
			ob_end_clean();
		}

		// Send initial heartbeat.
		echo "event: connected\n";
		echo "data: " . wp_json_encode( array( 'status' => 'ready' ) ) . "\n\n";
		flush();

		$full_response = '';
		$tokens_used   = 0;

		try {
			// Stream from OpenAI.
			$openai->stream_chat(
				$history,
				$message,
				function ( $chunk ) use ( &$full_response ) {
					$full_response .= $chunk;

					// Send chunk to client.
					echo "event: message\n";
					echo "data: " . wp_json_encode( array( 'chunk' => $chunk ) ) . "\n\n";
					flush();
				},
				function ( $tokens ) use ( &$tokens_used ) {
					$tokens_used = $tokens;
				}
			);

			// Store assistant message.
			$this->store_message( $session_id, 'assistant', $full_response, $tokens_used );

			// Send completion event.
			echo "event: done\n";
			echo "data: " . wp_json_encode( array( 'status' => 'complete', 'tokens' => $tokens_used ) ) . "\n\n";
			flush();

		} catch ( \Exception $e ) {
			// Generate request ID for correlation.
			$request_id = 'req_' . wp_generate_password( 16, false );

			// Map error to user-friendly message.
			$error_message = $this->map_error_message( $e->getMessage() );

			// Log error with context and redacted API key.
			$this->log_error(
				'stream_error',
				$request_id,
				$session_id,
				$e->getMessage(),
				array(
					'error_type'    => get_class( $e ),
					'error_code'    => $e->getCode(),
					'user_message'  => $error_message,
				)
			);

			// Store error for diagnostics.
			$this->store_error( 'stream_error', $error_message, $request_id );

			// Send error event.
			echo "event: error\n";
			echo "data: " . wp_json_encode( array(
				'error'      => $error_message,
				'type'       => 'stream_error',
				'request_id' => $request_id,
			) ) . "\n\n";
			flush();
		}

		exit;
	}

	/**
	 * Regular response (non-streaming fallback).
	 *
	 * @param OpenAIClient $openai OpenAI client.
	 * @param string       $session_id Session ID.
	 * @param array        $history Chat history.
	 * @param string       $message User message.
	 * @return WP_REST_Response|WP_Error
	 */
	private function regular_response( $openai, $session_id, $history, $message ) {
		try {
			$response = $openai->chat( $history, $message );

			// Store assistant message.
			$this->store_message( $session_id, 'assistant', $response['content'], $response['tokens'] );

			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => $response['content'],
					'tokens'  => $response['tokens'],
				),
				200
			);

		} catch ( \Exception $e ) {
			// Generate request ID for correlation.
			$request_id = 'req_' . wp_generate_password( 16, false );

			// Map error to user-friendly message.
			$error_message = $this->map_error_message( $e->getMessage() );

			// Log error with context and redacted API key.
			$this->log_error(
				'chat_error',
				$request_id,
				$session_id,
				$e->getMessage(),
				array(
					'error_type'    => get_class( $e ),
					'error_code'    => $e->getCode(),
					'user_message'  => $error_message,
				)
			);

			// Store error for diagnostics.
			$this->store_error( 'chat_error', $error_message, $request_id );

			return new WP_Error(
				'ai_error',
				$error_message,
				array(
					'status'     => 500,
					'request_id' => $request_id,
				)
			);
		}
	}

	/**
	 * Validate session.
	 *
	 * @param string $session_id Session ID.
	 * @return bool
	 */
	private function validate_session( $session_id ) {
		global $wpdb;

		$sessions_table = $wpdb->prefix . 'chatcommerce_sessions';
		$session        = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$sessions_table} WHERE session_id = %s",
				$session_id
			)
		);

		return ! empty( $session );
	}

	/**
	 * Update session activity.
	 *
	 * @param string $session_id Session ID.
	 */
	private function update_session_activity( $session_id ) {
		global $wpdb;

		$sessions_table = $wpdb->prefix . 'chatcommerce_sessions';
		$wpdb->update(
			$sessions_table,
			array(
				'last_activity'  => current_time( 'mysql' ),
				'message_count'  => $wpdb->get_var(
					$wpdb->prepare(
						"SELECT message_count FROM {$sessions_table} WHERE session_id = %s",
						$session_id
					)
				) + 1,
			),
			array( 'session_id' => $session_id ),
			array( '%s', '%d' ),
			array( '%s' )
		);
	}

	/**
	 * Store message.
	 *
	 * @param string $session_id Session ID.
	 * @param string $role Message role.
	 * @param string $content Message content.
	 * @param int    $tokens Tokens used.
	 */
	private function store_message( $session_id, $role, $content, $tokens = null ) {
		global $wpdb;

		$messages_table = $wpdb->prefix . 'chatcommerce_messages';
		$wpdb->insert(
			$messages_table,
			array(
				'session_id'  => $session_id,
				'role'        => $role,
				'content'     => $content,
				'tokens_used' => $tokens,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%s' )
		);
	}

	/**
	 * Get chat history.
	 *
	 * @param string $session_id Session ID.
	 * @param int    $limit Number of messages to retrieve.
	 * @return array
	 */
	private function get_chat_history( $session_id, $limit = 6 ) {
		global $wpdb;

		$messages_table = $wpdb->prefix . 'chatcommerce_messages';
		$messages       = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT role, content FROM {$messages_table} WHERE session_id = %s ORDER BY created_at DESC LIMIT %d",
				$session_id,
				$limit
			)
		);

		// Reverse to get chronological order.
		return array_reverse( array_map(
			function ( $msg ) {
				return array(
					'role'    => $msg->role,
					'content' => $msg->content,
				);
			},
			$messages
		) );
	}

	/**
	 * Map error message to user-friendly format.
	 *
	 * @param string $error_message Raw error message.
	 * @return string
	 */
	private function map_error_message( $error_message ) {
		// Common OpenAI error patterns.
		$error_patterns = array(
			'/401/'                          => __( 'Invalid API key. Please check your OpenAI API key in settings.', 'chatcommerce-ai' ),
			'/403/'                          => __( 'Access forbidden. Your API key may not have sufficient permissions.', 'chatcommerce-ai' ),
			'/429/'                          => __( 'Rate limit exceeded. Please try again in a moment.', 'chatcommerce-ai' ),
			'/500/'                          => __( 'OpenAI server error. Please try again later.', 'chatcommerce-ai' ),
			'/502/'                          => __( 'Bad gateway. OpenAI service may be temporarily unavailable.', 'chatcommerce-ai' ),
			'/503/'                          => __( 'Service unavailable. OpenAI is experiencing issues.', 'chatcommerce-ai' ),
			'/timeout/i'                     => __( 'Request timed out. Please try again.', 'chatcommerce-ai' ),
			'/network/i'                     => __( 'Network error. Please check your internet connection.', 'chatcommerce-ai' ),
			'/insufficient_quota/i'          => __( 'OpenAI quota exceeded. Please check your billing at OpenAI Platform.', 'chatcommerce-ai' ),
			'/invalid_request_error/i'       => __( 'Invalid request. Please contact support.', 'chatcommerce-ai' ),
			'/model_not_found/i'             => __( 'AI model not found. Please check your model settings.', 'chatcommerce-ai' ),
		);

		foreach ( $error_patterns as $pattern => $message ) {
			if ( preg_match( $pattern, $error_message ) ) {
				return $message;
			}
		}

		// Generic fallback.
		return __( 'An error occurred while processing your request. Please try again.', 'chatcommerce-ai' );
	}

	/**
	 * Log error with structured format and API key redaction.
	 *
	 * @param string $event_type Event type.
	 * @param string $request_id Request ID.
	 * @param string $session_id Session ID.
	 * @param string $error_message Error message.
	 * @param array  $context Additional context.
	 */
	private function log_error( $event_type, $request_id, $session_id, $error_message, $context = array() ) {
		// Redact API key from error message.
		$redacted_message = preg_replace( '/sk-[a-zA-Z0-9]{20,}/', 'sk-***REDACTED***', $error_message );

		// Add base context.
		$context = array_merge(
			array(
				'session_id' => $session_id,
				'timestamp'  => current_time( 'c' ),
				'user_id'    => get_current_user_id() ?: 'guest',
			),
			$context
		);

		// Use WordPress error_log with structured format.
		error_log(
			sprintf(
				'[ChatCommerce AI] [%s] [%s] %s | Context: %s',
				$event_type,
				$request_id,
				$redacted_message,
				wp_json_encode( $context )
			)
		);
	}

	/**
	 * Store error for diagnostics.
	 *
	 * @param string $code Error code.
	 * @param string $message Error message.
	 * @param string $request_id Request ID.
	 */
	private function store_error( $code, $message, $request_id ) {
		// Store last error.
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

		// Store in recent errors list (last 5).
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
}
