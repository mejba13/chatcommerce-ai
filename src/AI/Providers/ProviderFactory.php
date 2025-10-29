<?php
/**
 * AI Provider Factory
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\AI\Providers;

use ChatCommerceAI\AI\Providers\OpenAIProvider;
use ChatCommerceAI\AI\Providers\HuggingFaceProvider;

/**
 * Factory for creating AI provider instances.
 */
class ProviderFactory {
	/**
	 * Create a provider instance based on settings.
	 *
	 * @param array $settings Plugin settings.
	 * @return ChatProviderInterface
	 * @throws \Exception If provider is not supported or not configured.
	 */
	public static function create( $settings = null ) {
		if ( null === $settings ) {
			$settings = get_option( 'chatcommerce_ai_settings', array() );
		}

		$provider = $settings['ai_provider'] ?? 'openai';

		switch ( $provider ) {
			case 'openai':
				return new OpenAIProvider( $settings );

			case 'huggingface':
				return new HuggingFaceProvider( $settings );

			default:
				throw new \Exception(
					sprintf(
						/* translators: %s: provider name */
						__( 'Unsupported AI provider: %s', 'chatcommerce-ai' ),
						$provider
					)
				);
		}
	}

	/**
	 * Get list of available providers.
	 *
	 * @return array
	 */
	public static function get_available_providers() {
		return array(
			'openai'       => array(
				'name'        => __( 'OpenAI', 'chatcommerce-ai' ),
				'description' => __( 'GPT-4o, GPT-4o Mini, and other OpenAI models', 'chatcommerce-ai' ),
				'models'      => array(
					'gpt-4o-mini'         => __( 'GPT-4o Mini (Recommended - Fast & Affordable)', 'chatcommerce-ai' ),
					'gpt-4o'              => __( 'GPT-4o (Latest)', 'chatcommerce-ai' ),
					'gpt-4-turbo'         => __( 'GPT-4 Turbo', 'chatcommerce-ai' ),
					'gpt-4'               => __( 'GPT-4', 'chatcommerce-ai' ),
					'gpt-3.5-turbo'       => __( 'GPT-3.5 Turbo (Legacy)', 'chatcommerce-ai' ),
				),
				'docs_url'    => 'https://platform.openai.com/docs',
			),
			'huggingface'  => array(
				'name'        => __( 'Hugging Face', 'chatcommerce-ai' ),
				'description' => __( 'Open-source models via Hugging Face Inference API', 'chatcommerce-ai' ),
				'models'      => array(
					'mistralai/Mistral-7B-Instruct-v0.2' => __( 'Mistral 7B Instruct (Recommended)', 'chatcommerce-ai' ),
					'meta-llama/Meta-Llama-3.1-8B-Instruct' => __( 'Llama 3.1 8B Instruct', 'chatcommerce-ai' ),
					'meta-llama/Llama-3.2-3B-Instruct' => __( 'Llama 3.2 3B Instruct', 'chatcommerce-ai' ),
					'microsoft/Phi-3-mini-4k-instruct' => __( 'Phi-3 Mini 4K', 'chatcommerce-ai' ),
					'HuggingFaceH4/zephyr-7b-beta' => __( 'Zephyr 7B Beta', 'chatcommerce-ai' ),
				),
				'docs_url'    => 'https://huggingface.co/docs/api-inference',
			),
		);
	}
}
