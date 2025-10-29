<?php
/**
 * Hugging Face Provider
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\AI\Providers;

use ChatCommerceAI\Admin\AdminController;

/**
 * Hugging Face Inference API provider implementation.
 */
class HuggingFaceProvider implements ChatProviderInterface {
	/**
	 * API endpoint base.
	 *
	 * @var string
	 */
	private $api_base = 'https://api-inference.huggingface.co/models/';

	/**
	 * Access token.
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Model.
	 *
	 * @var string
	 */
	private $model;

	/**
	 * Temperature.
	 *
	 * @var float
	 */
	private $temperature;

	/**
	 * Max tokens.
	 *
	 * @var int
	 */
	private $max_tokens;

	/**
	 * Constructor.
	 *
	 * @param array $settings Plugin settings.
	 * @throws \Exception If access token is invalid.
	 */
	public function __construct( $settings ) {
		// Decrypt and validate access token.
		$decrypted_token = AdminController::decrypt_api_key( $settings['hf_access_token'] ?? '' );

		if ( false === $decrypted_token || empty( $decrypted_token ) ) {
			throw new \Exception( __( 'Hugging Face access token is not configured or failed to decrypt.', 'chatcommerce-ai' ) );
		}

		if ( strpos( $decrypted_token, 'hf_' ) !== 0 ) {
			throw new \Exception( __( 'Invalid Hugging Face access token format. Token should start with "hf_".', 'chatcommerce-ai' ) );
		}

		$this->access_token = $decrypted_token;
		$this->model        = $settings['hf_model'] ?? 'mistralai/Mistral-7B-Instruct-v0.2';
		$this->temperature  = floatval( $settings['temperature'] ?? 0.7 );
		$this->max_tokens   = intval( $settings['max_tokens'] ?? 500 );
	}

	/**
	 * Get provider name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Hugging Face';
	}

	/**
	 * Test connection.
	 *
	 * @return array
	 */
	public function test_connection() {
		$start_time = microtime( true );

		$endpoint = $this->api_base . $this->model;

		$response = wp_remote_post(
			$endpoint,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'inputs'     => 'test',
						'parameters' => array(
							'max_new_tokens' => 5,
							'temperature'    => 0.7,
							'return_full_text' => false,
						),
					)
				),
				'timeout' => 25,
			)
		);

		$latency = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		if ( is_wp_error( $response ) ) {
			return array(
				'success'    => false,
				'message'    => $response->get_error_message(),
				'latency_ms' => $latency,
				'error'      => array(
					'type' => 'network_error',
					'code' => $response->get_error_code(),
				),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( $status_code !== 200 ) {
			$error_message = $data['error'] ?? sprintf( 'HTTP %d', $status_code );

			// Map common HF errors.
			if ( $status_code === 401 || $status_code === 403 ) {
				$error_message = __( 'Invalid access token. Please check your Hugging Face access token.', 'chatcommerce-ai' );
			} elseif ( $status_code === 404 ) {
				$error_message = __( 'Model not found. Please check the model name.', 'chatcommerce-ai' );
			} elseif ( $status_code === 429 ) {
				$error_message = __( 'Rate limit exceeded. Please try again later.', 'chatcommerce-ai' );
			} elseif ( $status_code >= 500 ) {
				$error_message = __( 'Hugging Face service error. Please try again later.', 'chatcommerce-ai' );
			}

			return array(
				'success'    => false,
				'message'    => $error_message,
				'latency_ms' => $latency,
				'error'      => array(
					'type'        => 'api_error',
					'code'        => $status_code,
					'api_error'   => $data['error'] ?? null,
				),
			);
		}

		return array(
			'success'    => true,
			'message'    => __( 'Connection successful!', 'chatcommerce-ai' ),
			'latency_ms' => $latency,
			'model'      => $this->model,
		);
	}

	/**
	 * Generate response.
	 *
	 * @param array  $history Chat history.
	 * @param string $message User message.
	 * @return array
	 * @throws \Exception If generation fails.
	 */
	public function generate( $history, $message ) {
		$prompt = $this->build_prompt( $history, $message );

		$endpoint = $this->api_base . $this->model;

		$response = wp_remote_post(
			$endpoint,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'inputs'     => $prompt,
						'parameters' => array(
							'max_new_tokens'   => $this->max_tokens,
							'temperature'      => $this->temperature,
							'return_full_text' => false,
							'do_sample'        => true,
						),
					)
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( $status_code !== 200 ) {
			$error_message = $data['error'] ?? sprintf( 'HTTP %d', $status_code );
			throw new \Exception( $error_message );
		}

		// HF Inference API returns array of results.
		$content = '';
		if ( isset( $data[0]['generated_text'] ) ) {
			$content = $data[0]['generated_text'];
		} elseif ( is_string( $data ) ) {
			$content = $data;
		}

		if ( empty( $content ) ) {
			throw new \Exception( 'No response from AI' );
		}

		// Estimate tokens (rough approximation).
		$tokens = ceil( strlen( $content ) / 4 );

		return array(
			'content' => trim( $content ),
			'tokens'  => $tokens,
		);
	}

	/**
	 * Stream response.
	 *
	 * Note: Hugging Face Inference API has limited streaming support.
	 * This implementation uses chunked responses as a fallback.
	 *
	 * @param array    $history         Chat history.
	 * @param string   $message         User message.
	 * @param callable $chunk_callback  Callback for chunks.
	 * @param callable $done_callback   Callback when done.
	 * @throws \Exception If streaming fails.
	 */
	public function stream( $history, $message, $chunk_callback, $done_callback ) {
		// For now, use non-streaming and send as one chunk.
		$result = $this->generate( $history, $message );

		// Send content as single chunk.
		call_user_func( $chunk_callback, $result['content'] );

		// Call done callback.
		call_user_func( $done_callback, $result['tokens'] );
	}

	/**
	 * Check if streaming is supported.
	 *
	 * @return bool
	 */
	public function supports_streaming() {
		// HF Inference API has limited streaming support, so we return false.
		return false;
	}

	/**
	 * Get provider configuration.
	 *
	 * @return array
	 */
	public function get_config() {
		return array(
			'provider'    => 'huggingface',
			'model'       => $this->model,
			'temperature' => $this->temperature,
			'max_tokens'  => $this->max_tokens,
		);
	}

	/**
	 * Build prompt from history and message.
	 *
	 * @param array  $history Chat history.
	 * @param string $message New message.
	 * @return string
	 */
	private function build_prompt( $history, $message ) {
		$system_prompt = $this->get_system_prompt();

		// Build conversation format.
		$prompt = $system_prompt . "\n\n";

		// Add history.
		foreach ( $history as $msg ) {
			$role = $msg['role'] === 'user' ? 'User' : 'Assistant';
			$prompt .= "{$role}: {$msg['content']}\n\n";
		}

		// Add new message.
		$prompt .= "User: {$message}\n\nAssistant:";

		return $prompt;
	}

	/**
	 * Get system prompt.
	 *
	 * @return string
	 */
	private function get_system_prompt() {
		$active_version = get_option( 'chatcommerce_ai_prompt_active', 'v1' );
		$prompt         = get_option( "chatcommerce_ai_prompt_{$active_version}", '' );

		// Replace variables.
		$prompt = str_replace(
			array( '{site_name}', '{store_url}', '{currency}' ),
			array(
				get_bloginfo( 'name' ),
				get_home_url(),
				get_woocommerce_currency(),
			),
			$prompt
		);

		if ( empty( $prompt ) ) {
			$prompt = sprintf(
				"You are a helpful customer service assistant for %s. Answer questions about products, orders, and store policies.",
				get_bloginfo( 'name' )
			);
		}

		return $prompt;
	}
}
