<?php
/**
 * OpenAI Provider
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\AI\Providers;

use ChatCommerceAI\Admin\AdminController;

/**
 * OpenAI chat provider implementation.
 */
class OpenAIProvider implements ChatProviderInterface {
	/**
	 * API endpoint.
	 *
	 * @var string
	 */
	private $api_endpoint = 'https://api.openai.com/v1/chat/completions';

	/**
	 * API key.
	 *
	 * @var string
	 */
	private $api_key;

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
	 * @throws \Exception If API key is invalid.
	 */
	public function __construct( $settings ) {
		// Decrypt and validate API key.
		$decrypted_key = AdminController::decrypt_api_key( $settings['openai_api_key'] ?? '' );

		if ( false === $decrypted_key || empty( $decrypted_key ) ) {
			throw new \Exception( __( 'OpenAI API key is not configured or failed to decrypt.', 'chatcommerce-ai' ) );
		}

		if ( strpos( $decrypted_key, 'sk-' ) !== 0 ) {
			throw new \Exception( __( 'Invalid OpenAI API key format.', 'chatcommerce-ai' ) );
		}

		$this->api_key     = $decrypted_key;
		$this->model       = $settings['openai_model'] ?? 'gpt-4o-mini';
		$this->temperature = floatval( $settings['temperature'] ?? 0.7 );
		$this->max_tokens  = intval( $settings['max_tokens'] ?? 500 );
	}

	/**
	 * Get provider name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'OpenAI';
	}

	/**
	 * Test connection.
	 *
	 * @return array
	 */
	public function test_connection() {
		$start_time = microtime( true );

		$response = wp_remote_post(
			$this->api_endpoint,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => $this->model,
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
			return array(
				'success'    => false,
				'message'    => $data['error']['message'] ?? sprintf( 'HTTP %d', $status_code ),
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
			'tokens'     => $data['usage']['total_tokens'] ?? 0,
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
		$messages = $this->build_messages( $history, $message );

		$body = array(
			'model'       => $this->model,
			'messages'    => $messages,
			'temperature' => $this->temperature,
			'max_tokens'  => $this->max_tokens,
		);

		$response = wp_remote_post(
			$this->api_endpoint,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $data['error'] ) ) {
			throw new \Exception( $data['error']['message'] ?? 'Unknown error' );
		}

		$choice = $data['choices'][0] ?? null;

		if ( ! $choice ) {
			throw new \Exception( 'No response from AI' );
		}

		return array(
			'content' => $choice['message']['content'],
			'tokens'  => $data['usage']['total_tokens'] ?? 0,
		);
	}

	/**
	 * Stream response.
	 *
	 * @param array    $history         Chat history.
	 * @param string   $message         User message.
	 * @param callable $chunk_callback  Callback for chunks.
	 * @param callable $done_callback   Callback when done.
	 * @throws \Exception If streaming fails.
	 */
	public function stream( $history, $message, $chunk_callback, $done_callback ) {
		$messages = $this->build_messages( $history, $message );

		$body = array(
			'model'       => $this->model,
			'messages'    => $messages,
			'temperature' => $this->temperature,
			'max_tokens'  => $this->max_tokens,
			'stream'      => true,
		);

		$ch = curl_init( $this->api_endpoint );

		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, wp_json_encode( $body ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				'Authorization: Bearer ' . $this->api_key,
				'Content-Type: application/json',
			)
		);

		$full_content = '';
		$tokens_used  = 0;

		curl_setopt(
			$ch,
			CURLOPT_WRITEFUNCTION,
			function ( $curl, $data ) use ( &$full_content, $chunk_callback ) {
				$lines = explode( "\n", $data );

				foreach ( $lines as $line ) {
					if ( strpos( $line, 'data: ' ) === 0 ) {
						$json = substr( $line, 6 );

						if ( trim( $json ) === '[DONE]' ) {
							continue;
						}

						$decoded = json_decode( $json, true );

						if ( isset( $decoded['choices'][0]['delta']['content'] ) ) {
							$content = $decoded['choices'][0]['delta']['content'];
							$full_content .= $content;
							call_user_func( $chunk_callback, $content );
						}
					}
				}

				return strlen( $data );
			}
		);

		$result = curl_exec( $ch );

		if ( $result === false ) {
			throw new \Exception( curl_error( $ch ) );
		}

		curl_close( $ch );

		// Estimate tokens.
		$tokens_used = ceil( strlen( $full_content ) / 4 );

		call_user_func( $done_callback, $tokens_used );
	}

	/**
	 * Check if streaming is supported.
	 *
	 * @return bool
	 */
	public function supports_streaming() {
		return true;
	}

	/**
	 * Get provider configuration.
	 *
	 * @return array
	 */
	public function get_config() {
		return array(
			'provider'    => 'openai',
			'model'       => $this->model,
			'temperature' => $this->temperature,
			'max_tokens'  => $this->max_tokens,
		);
	}

	/**
	 * Build messages array.
	 *
	 * @param array  $history Chat history.
	 * @param string $message New message.
	 * @return array
	 */
	private function build_messages( $history, $message ) {
		$messages = array();

		// System prompt.
		$system_prompt = $this->get_system_prompt();
		$messages[]    = array(
			'role'    => 'system',
			'content' => $system_prompt,
		);

		// History.
		foreach ( $history as $msg ) {
			$messages[] = array(
				'role'    => $msg['role'],
				'content' => $msg['content'],
			);
		}

		// New message.
		$messages[] = array(
			'role'    => 'user',
			'content' => $message,
		);

		return $messages;
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

		return $prompt;
	}
}
