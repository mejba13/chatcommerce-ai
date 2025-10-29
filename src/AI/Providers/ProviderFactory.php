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
					'HuggingFaceH4/zephyr-7b-beta' => __( 'Zephyr 7B Beta (Recommended - Free)', 'chatcommerce-ai' ),
					'microsoft/DialoGPT-large' => __( 'DialoGPT Large (Conversational)', 'chatcommerce-ai' ),
					'google/flan-t5-xxl' => __( 'FLAN-T5 XXL (11B params)', 'chatcommerce-ai' ),
					'google/flan-t5-large' => __( 'FLAN-T5 Large (Fast)', 'chatcommerce-ai' ),
					'meta-llama/Llama-2-7b-chat-hf' => __( 'Llama 2 7B Chat (Requires License)', 'chatcommerce-ai' ),
					'meta-llama/Meta-Llama-3-8B-Instruct' => __( 'Llama 3 8B Instruct', 'chatcommerce-ai' ),
					'mistralai/Mistral-7B-Instruct-v0.1' => __( 'Mistral 7B Instruct v0.1', 'chatcommerce-ai' ),
					'mistralai/Mistral-7B-Instruct-v0.2' => __( 'Mistral 7B Instruct v0.2', 'chatcommerce-ai' ),
					'tiiuae/falcon-7b-instruct' => __( 'Falcon 7B Instruct', 'chatcommerce-ai' ),
					'bigscience/bloom-560m' => __( 'BLOOM 560M (Lightweight)', 'chatcommerce-ai' ),
					'deepseek-ai/DeepSeek-OCR' => __( 'DeepSeek OCR (Multimodal)', 'chatcommerce-ai' ),
					'microsoft/Phi-3-mini-4k-instruct' => __( 'Phi-3 Mini 4K Instruct', 'chatcommerce-ai' ),
				),
				'docs_url'    => 'https://huggingface.co/docs/api-inference',
			),
		);
	}
}
