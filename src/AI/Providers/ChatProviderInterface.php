<?php
/**
 * Chat Provider Interface
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\AI\Providers;

/**
 * Interface for AI chat providers.
 */
interface ChatProviderInterface {
	/**
	 * Get provider name.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Test connection to the provider.
	 *
	 * @return array {
	 *     @type bool   $success    Whether the connection was successful.
	 *     @type string $message    Status message.
	 *     @type float  $latency_ms Latency in milliseconds.
	 *     @type string $model      Model used for test.
	 *     @type array  $error      Error details if failed.
	 * }
	 */
	public function test_connection();

	/**
	 * Generate a chat response (non-streaming).
	 *
	 * @param array  $history Chat history.
	 * @param string $message User message.
	 * @return array {
	 *     @type string $content Response content.
	 *     @type int    $tokens  Tokens used.
	 * }
	 * @throws \Exception If generation fails.
	 */
	public function generate( $history, $message );

	/**
	 * Stream a chat response.
	 *
	 * @param array    $history         Chat history.
	 * @param string   $message         User message.
	 * @param callable $chunk_callback  Callback for each chunk.
	 * @param callable $done_callback   Callback when done.
	 * @throws \Exception If streaming fails.
	 */
	public function stream( $history, $message, $chunk_callback, $done_callback );

	/**
	 * Check if provider supports streaming.
	 *
	 * @return bool
	 */
	public function supports_streaming();

	/**
	 * Get provider-specific configuration.
	 *
	 * @return array
	 */
	public function get_config();
}
