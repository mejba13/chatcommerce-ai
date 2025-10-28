<?php
/**
 * OpenAI Client
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\AI;

use ChatCommerceAI\Admin\AdminController;
use ChatCommerceAI\AI\Tools\ProductSearchTool;
use ChatCommerceAI\AI\Tools\StockCheckTool;
use ChatCommerceAI\AI\Tools\PolicyTool;

/**
 * OpenAI API client with function calling.
 */
class OpenAIClient {
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
	 */
	public function __construct() {
		$settings = get_option( 'chatcommerce_ai_settings', array() );

		$this->api_key     = AdminController::decrypt_api_key( $settings['openai_api_key'] ?? '' );
		$this->model       = $settings['openai_model'] ?? 'gpt-4-turbo-preview';
		$this->temperature = floatval( $settings['temperature'] ?? 0.7 );
		$this->max_tokens  = intval( $settings['max_tokens'] ?? 500 );
	}

	/**
	 * Send chat request (non-streaming).
	 *
	 * @param array  $history Chat history.
	 * @param string $message User message.
	 * @return array
	 * @throws \Exception If API request fails.
	 */
	public function chat( $history, $message ) {
		$messages = $this->build_messages( $history, $message );
		$tools    = $this->get_tools();

		$body = array(
			'model'       => $this->model,
			'messages'    => $messages,
			'temperature' => $this->temperature,
			'max_tokens'  => $this->max_tokens,
		);

		if ( ! empty( $tools ) ) {
			$body['tools'] = $tools;
		}

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

		// Handle function calls.
		if ( isset( $choice['message']['tool_calls'] ) ) {
			return $this->handle_function_calls( $choice['message']['tool_calls'], $messages, $history, $message );
		}

		return array(
			'content' => $choice['message']['content'],
			'tokens'  => $data['usage']['total_tokens'] ?? 0,
		);
	}

	/**
	 * Stream chat response.
	 *
	 * @param array    $history Chat history.
	 * @param string   $message User message.
	 * @param callable $chunk_callback Callback for each chunk.
	 * @param callable $done_callback Callback when done.
	 * @throws \Exception If API request fails.
	 */
	public function stream_chat( $history, $message, $chunk_callback, $done_callback ) {
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

		// Estimate tokens (rough approximation: 1 token ≈ 4 characters).
		$tokens_used = ceil( strlen( $full_content ) / 4 );

		call_user_func( $done_callback, $tokens_used );
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

	/**
	 * Get available tools.
	 *
	 * @return array
	 */
	private function get_tools() {
		$settings = get_option( 'chatcommerce_ai_settings', array() );

		if ( empty( $settings['function_calling'] ) && isset( $settings['function_calling'] ) ) {
			return array();
		}

		return array(
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'find_product',
					'description' => 'Search for products by name, SKU, or category',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'query' => array(
								'type'        => 'string',
								'description' => 'Search query (product name, SKU, or category)',
							),
						),
						'required'   => array( 'query' ),
					),
				),
			),
			array(
				'type'     => 'function',
				'function' => array(
					'name'        => 'check_stock',
					'description' => 'Check stock status of a product',
					'parameters'  => array(
						'type'       => 'object',
						'properties' => array(
							'product_id' => array(
								'type'        => 'integer',
								'description' => 'Product ID',
							),
						),
						'required'   => array( 'product_id' ),
					),
				),
			),
		);
	}

	/**
	 * Handle function calls.
	 *
	 * @param array  $tool_calls Tool calls from AI.
	 * @param array  $messages Previous messages.
	 * @param array  $history Chat history.
	 * @param string $user_message User message.
	 * @return array
	 * @throws \Exception If function call fails.
	 */
	private function handle_function_calls( $tool_calls, $messages, $history, $user_message ) {
		$function_results = array();

		foreach ( $tool_calls as $tool_call ) {
			$function_name = $tool_call['function']['name'];
			$arguments     = json_decode( $tool_call['function']['arguments'], true );

			$result = $this->execute_function( $function_name, $arguments );

			$function_results[] = array(
				'tool_call_id' => $tool_call['id'],
				'role'         => 'tool',
				'name'         => $function_name,
				'content'      => wp_json_encode( $result ),
			);
		}

		// Make a second API call with function results.
		$messages = array_merge( $messages, $function_results );

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

		$data   = json_decode( wp_remote_retrieve_body( $response ), true );
		$choice = $data['choices'][0] ?? null;

		return array(
			'content' => $choice['message']['content'],
			'tokens'  => $data['usage']['total_tokens'] ?? 0,
		);
	}

	/**
	 * Execute function.
	 *
	 * @param string $function_name Function name.
	 * @param array  $arguments Arguments.
	 * @return array
	 */
	private function execute_function( $function_name, $arguments ) {
		switch ( $function_name ) {
			case 'find_product':
				$tool = new ProductSearchTool();
				return $tool->search( $arguments['query'] ?? '' );

			case 'check_stock':
				$tool = new StockCheckTool();
				return $tool->check( $arguments['product_id'] ?? 0 );

			default:
				return array( 'error' => 'Unknown function' );
		}
	}
}
