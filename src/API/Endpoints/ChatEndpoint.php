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
			// Send error event.
			echo "event: error\n";
			echo "data: " . wp_json_encode( array( 'error' => $e->getMessage() ) ) . "\n\n";
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
			return new WP_Error(
				'ai_error',
				$e->getMessage(),
				array( 'status' => 500 )
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
}
