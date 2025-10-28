<?php
/**
 * AI Settings Tab
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChatCommerceAI\Admin\AdminController;

$has_api_key = ! empty( $settings['openai_api_key'] );

?>

<h2><?php esc_html_e( 'AI Settings', 'chatcommerce-ai' ); ?></h2>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<?php if ( $has_api_key ) : ?>
				<input
					type="password"
					name="chatcommerce_ai[openai_api_key]"
					id="openai_api_key"
					value="••••••••••••••••"
					class="api-key-input"
					placeholder="sk-..."
				/>
				<input
					type="hidden"
					name="chatcommerce_ai[openai_api_key_encrypted]"
					value="<?php echo esc_attr( $settings['openai_api_key'] ); ?>"
				/>
				<p class="setting-description">
					<?php esc_html_e( 'API key is set. Enter a new key to replace it, or leave blank to keep the existing key.', 'chatcommerce-ai' ); ?>
				</p>
			<?php else : ?>
				<input
					type="text"
					name="chatcommerce_ai[openai_api_key]"
					id="openai_api_key"
					value=""
					class="api-key-input"
					placeholder="sk-..."
				/>
				<p class="setting-description">
					<?php
					printf(
						/* translators: %s: OpenAI API keys URL */
						__( 'Get your API key from <a href="%s" target="_blank">OpenAI Platform</a>.', 'chatcommerce-ai' ),
						'https://platform.openai.com/api-keys'
					);
					?>
				</p>
			<?php endif; ?>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="openai_model"><?php esc_html_e( 'AI Model', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[openai_model]" id="openai_model">
				<option value="gpt-4o-mini" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-4o-mini' ); ?>>
					GPT-4o Mini (Recommended - Fast & Affordable)
				</option>
				<option value="gpt-4-turbo-preview" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-4-turbo-preview' ); ?>>
					GPT-4 Turbo
				</option>
				<option value="gpt-4o" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-4o' ); ?>>
					GPT-4o (Latest)
				</option>
				<option value="gpt-4" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-4' ); ?>>
					GPT-4
				</option>
				<option value="gpt-3.5-turbo" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-3.5-turbo' ); ?>>
					GPT-3.5 Turbo (Legacy)
				</option>
			</select>
			<p class="setting-description">
				<?php esc_html_e( 'GPT-4o Mini is recommended for most use cases - it offers excellent performance at a low cost. GPT-4o is more capable but costs more.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="temperature"><?php esc_html_e( 'Temperature', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<input
				type="number"
				name="chatcommerce_ai[temperature]"
				id="temperature"
				value="<?php echo esc_attr( $settings['temperature'] ?? 0.7 ); ?>"
				min="0"
				max="2"
				step="0.1"
				style="width: 100px;"
			/>
			<span class="setting-description" style="margin-left: 10px;">
				<?php esc_html_e( '0.0 - 2.0', 'chatcommerce-ai' ); ?>
			</span>
			<p class="setting-description">
				<?php esc_html_e( 'Controls randomness. Lower values make responses more focused and deterministic, higher values make them more creative. Recommended: 0.7', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="max_tokens"><?php esc_html_e( 'Max Tokens', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<input
				type="number"
				name="chatcommerce_ai[max_tokens]"
				id="max_tokens"
				value="<?php echo esc_attr( $settings['max_tokens'] ?? 500 ); ?>"
				min="50"
				max="4000"
				step="50"
				style="width: 100px;"
			/>
			<p class="setting-description">
				<?php esc_html_e( 'Maximum length of AI responses. Higher values allow longer responses but cost more. Recommended: 500', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="function_calling"><?php esc_html_e( 'Function Calling', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[function_calling]"
					id="function_calling"
					value="1"
					<?php checked( ! empty( $settings['function_calling'] ) || ! isset( $settings['function_calling'] ) ); ?>
				/>
				<?php esc_html_e( 'Enable function calling for product lookup and store info', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Allows the AI to use tools to find products, check stock, and retrieve store policies. Recommended to keep enabled.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label><?php esc_html_e( 'Safety Filters', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[safety_filters]"
					value="1"
					<?php checked( ! empty( $settings['safety_filters'] ) || ! isset( $settings['safety_filters'] ) ); ?>
				/>
				<?php esc_html_e( 'Enable content safety filters', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Prevents the AI from generating inappropriate content. Recommended to keep enabled.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>
</table>

<div class="notice notice-info inline" style="margin-top: 20px;">
	<p>
		<strong><?php esc_html_e( 'About API Costs:', 'chatcommerce-ai' ); ?></strong>
		<?php esc_html_e( 'You will be charged by OpenAI based on the number of tokens used. Monitor your usage at OpenAI Platform.', 'chatcommerce-ai' ); ?>
	</p>
</div>
