<?php
/**
 * Test script for provider system
 * Usage: php test-providers.php
 */

// Load WordPress.
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

use ChatCommerceAI\AI\Providers\ProviderFactory;

echo "=== ChatCommerce AI Provider Test ===\n\n";

// Get settings.
$settings = get_option( 'chatcommerce_ai_settings', array() );
$provider_key = $settings['ai_provider'] ?? 'openai';

echo "Current provider: {$provider_key}\n";
echo "OpenAI model: " . ( $settings['openai_model'] ?? 'not set' ) . "\n";
echo "HF model: " . ( $settings['hf_model'] ?? 'not set' ) . "\n\n";

// Test provider creation.
try {
	echo "Creating provider instance...\n";
	$provider = ProviderFactory::create( $settings );

	echo "✓ Provider created successfully\n";
	echo "Provider name: " . $provider->get_name() . "\n";
	echo "Supports streaming: " . ( $provider->supports_streaming() ? 'Yes' : 'No' ) . "\n";

	$config = $provider->get_config();
	echo "\nProvider config:\n";
	print_r( $config );

	// Test connection.
	echo "\nTesting connection...\n";
	$result = $provider->test_connection();

	if ( $result['success'] ) {
		echo "✓ Connection successful!\n";
		echo "Latency: {$result['latency_ms']}ms\n";
		echo "Model: {$result['model']}\n";
	} else {
		echo "✗ Connection failed: {$result['message']}\n";
	}

} catch ( \Exception $e ) {
	echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
