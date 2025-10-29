<?php
/**
 * AI Settings Tab - Multi-Provider Support
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChatCommerceAI\Admin\AdminController;
use ChatCommerceAI\AI\Providers\ProviderFactory;

$current_provider = $settings['ai_provider'] ?? 'openai';
$has_openai_key = ! empty( $settings['openai_api_key'] );
$has_hf_token = ! empty( $settings['hf_access_token'] );
$providers = ProviderFactory::get_available_providers();

?>

<h2><?php esc_html_e( 'AI Settings', 'chatcommerce-ai' ); ?></h2>

<table class="form-table">
	<!-- AI Provider Selector -->
	<tr>
		<th scope="row">
			<label for="ai_provider"><?php esc_html_e( 'AI Provider', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[ai_provider]" id="ai_provider" class="cc-form-select" style="max-width: 400px;">
				<?php foreach ( $providers as $provider_key => $provider_info ) : ?>
					<option value="<?php echo esc_attr( $provider_key ); ?>" <?php selected( $current_provider, $provider_key ); ?>>
						<?php echo esc_html( $provider_info['name'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description" id="provider-description">
				<?php echo esc_html( $providers[ $current_provider ]['description'] ?? '' ); ?>
			</p>
		</td>
	</tr>

	<!-- OpenAI Settings -->
	<tr class="provider-settings" data-provider="openai" style="<?php echo 'openai' === $current_provider ? '' : 'display:none;'; ?>">
		<th scope="row">
			<label for="openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<div class="cc-api-key-wrapper">
				<?php if ( $has_openai_key ) : ?>
					<input
						type="password"
						name="chatcommerce_ai[openai_api_key]"
						id="openai_api_key"
						value="••••••••••••••••••••••••••••••••"
						class="cc-api-key-input"
						placeholder="sk-..."
						data-has-key="true"
					/>
					<input
						type="hidden"
						name="chatcommerce_ai[openai_api_key_encrypted]"
						value="<?php echo esc_attr( $settings['openai_api_key'] ); ?>"
					/>
					<button type="button" class="cc-toggle-api-key" aria-label="<?php esc_attr_e( 'Toggle API key visibility', 'chatcommerce-ai' ); ?>">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</button>
				<?php else : ?>
					<input
						type="password"
						name="chatcommerce_ai[openai_api_key]"
						id="openai_api_key"
						value=""
						class="cc-api-key-input"
						placeholder="sk-..."
					/>
					<button type="button" class="cc-toggle-api-key" aria-label="<?php esc_attr_e( 'Toggle API key visibility', 'chatcommerce-ai' ); ?>">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
			</div>
			<p class="description">
				<?php
				printf(
					/* translators: %s: OpenAI API keys URL */
					__( 'Get your API key from <a href="%s" target="_blank">OpenAI Platform</a>.', 'chatcommerce-ai' ),
					'https://platform.openai.com/api-keys'
				);
				?>
			</p>

			<?php if ( $has_openai_key ) : ?>
				<div style="margin-top: 12px;">
					<button
						type="button"
						id="cc-test-connection-openai-btn"
						class="button button-secondary cc-test-connection-btn"
						data-provider="openai"
						style="display: inline-flex; align-items: center; gap: 8px;"
					>
						<span class="dashicons dashicons-admin-plugins" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
						<?php esc_html_e( 'Test Connection', 'chatcommerce-ai' ); ?>
					</button>
					<span class="cc-test-connection-spinner spinner" style="float: none; margin: 0 0 0 8px; display: none;"></span>
				</div>
				<div class="cc-test-connection-result" style="margin-top: 12px; display: none;"></div>
			<?php endif; ?>
		</td>
	</tr>

	<tr class="provider-settings" data-provider="openai" style="<?php echo 'openai' === $current_provider ? '' : 'display:none;'; ?>">
		<th scope="row">
			<label for="openai_model"><?php esc_html_e( 'OpenAI Model', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[openai_model]" id="openai_model" class="cc-form-select" style="max-width: 400px;">
				<?php foreach ( $providers['openai']['models'] as $model_key => $model_label ) : ?>
					<option value="<?php echo esc_attr( $model_key ); ?>" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', $model_key ); ?>>
						<?php echo esc_html( $model_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description">
				<?php esc_html_e( 'GPT-4o Mini is recommended for most use cases - excellent performance at low cost.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<!-- Hugging Face Settings -->
	<tr class="provider-settings" data-provider="huggingface" style="<?php echo 'huggingface' === $current_provider ? '' : 'display:none;'; ?>">
		<th scope="row">
			<label for="hf_access_token"><?php esc_html_e( 'Hugging Face Access Token', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<div class="cc-api-key-wrapper">
				<?php if ( $has_hf_token ) : ?>
					<input
						type="password"
						name="chatcommerce_ai[hf_access_token]"
						id="hf_access_token"
						value="••••••••••••••••••••••••••••••••"
						class="cc-api-key-input"
						placeholder="hf_..."
						data-has-key="true"
					/>
					<input
						type="hidden"
						name="chatcommerce_ai[hf_access_token_encrypted]"
						value="<?php echo esc_attr( $settings['hf_access_token'] ); ?>"
					/>
					<button type="button" class="cc-toggle-api-key" aria-label="<?php esc_attr_e( 'Toggle token visibility', 'chatcommerce-ai' ); ?>">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</button>
				<?php else : ?>
					<input
						type="password"
						name="chatcommerce_ai[hf_access_token]"
						id="hf_access_token"
						value=""
						class="cc-api-key-input"
						placeholder="hf_..."
					/>
					<button type="button" class="cc-toggle-api-key" aria-label="<?php esc_attr_e( 'Toggle token visibility', 'chatcommerce-ai' ); ?>">
						<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
			</div>
			<p class="description">
				<?php
				printf(
					/* translators: %s: Hugging Face tokens URL */
					__( 'Get your access token from <a href="%s" target="_blank">Hugging Face Settings</a>.', 'chatcommerce-ai' ),
					'https://huggingface.co/settings/tokens'
				);
				?>
			</p>

			<?php if ( $has_hf_token ) : ?>
				<div style="margin-top: 12px;">
					<button
						type="button"
						id="cc-test-connection-hf-btn"
						class="button button-secondary cc-test-connection-btn"
						data-provider="huggingface"
						style="display: inline-flex; align-items: center; gap: 8px;"
					>
						<span class="dashicons dashicons-admin-plugins" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
						<?php esc_html_e( 'Test Connection', 'chatcommerce-ai' ); ?>
					</button>
					<span class="cc-test-connection-spinner spinner" style="float: none; margin: 0 0 0 8px; display: none;"></span>
				</div>
				<div class="cc-test-connection-result" style="margin-top: 12px; display: none;"></div>
			<?php endif; ?>
		</td>
	</tr>

	<tr class="provider-settings" data-provider="huggingface" style="<?php echo 'huggingface' === $current_provider ? '' : 'display:none;'; ?>">
		<th scope="row">
			<label for="hf_model"><?php esc_html_e( 'Hugging Face Model', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[hf_model]" id="hf_model" class="cc-form-select" style="max-width: 400px;">
				<?php foreach ( $providers['huggingface']['models'] as $model_key => $model_label ) : ?>
					<option value="<?php echo esc_attr( $model_key ); ?>" <?php selected( $settings['hf_model'] ?? 'HuggingFaceH4/zephyr-7b-beta', $model_key ); ?>>
						<?php echo esc_html( $model_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description">
				<?php esc_html_e( 'Mistral 7B is recommended - good balance of performance and speed. Note: Streaming not supported for Hugging Face.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<!-- Shared Settings -->
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
				<?php esc_html_e( 'Controls randomness. Lower values = more focused, higher values = more creative. Recommended: 0.7', 'chatcommerce-ai' ); ?>
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

	<tr class="provider-settings" data-provider="openai" style="<?php echo 'openai' === $current_provider ? '' : 'display:none;'; ?>">
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
				<?php esc_html_e( 'Allows the AI to use tools to find products, check stock, and retrieve store policies. OpenAI only.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr class="provider-settings" data-provider="openai" style="<?php echo 'openai' === $current_provider ? '' : 'display:none;'; ?>">
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
				<?php esc_html_e( 'Prevents the AI from generating inappropriate content. Recommended.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>
</table>

<div class="notice notice-info inline" style="margin-top: 20px;">
	<p>
		<strong><?php esc_html_e( 'About Costs:', 'chatcommerce-ai' ); ?></strong>
		<span id="cost-notice-openai" style="<?php echo 'openai' === $current_provider ? '' : 'display:none;'; ?>">
			<?php esc_html_e( 'You will be charged by OpenAI based on tokens used. Monitor usage at OpenAI Platform.', 'chatcommerce-ai' ); ?>
		</span>
		<span id="cost-notice-huggingface" style="<?php echo 'huggingface' === $current_provider ? '' : 'display:none;'; ?>">
			<?php esc_html_e( 'Hugging Face Inference API has rate limits. Check your usage at Hugging Face Settings.', 'chatcommerce-ai' ); ?>
		</span>
	</p>
</div>

<style>
@keyframes pulse {
	0%, 100% {
		opacity: 1;
	}
	50% {
		opacity: 0.5;
	}
}

.status-indicator {
	transition: all 0.3s ease-in-out;
}

.status-text {
	transition: color 0.3s ease-in-out;
}

.status-details {
	transition: all 0.3s ease-in-out;
}

/* Smooth fade transitions */
.cc-test-connection-result {
	animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
	from {
		opacity: 0;
		transform: translateY(-4px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}
</style>

<script>
(function($) {
	// Provider switching
	$('#ai_provider').on('change', function() {
		const provider = $(this).val();

		// Hide all provider-specific settings
		$('.provider-settings').hide();

		// Show selected provider settings
		$('.provider-settings[data-provider="' + provider + '"]').show();

		// Update description
		const descriptions = <?php echo wp_json_encode( array_map( function( $p ) { return $p['description']; }, $providers ) ); ?>;
		$('#provider-description').text(descriptions[provider] || '');

		// Update cost notice
		$('#cost-notice-openai, #cost-notice-huggingface').hide();
		$('#cost-notice-' + provider).show();
	});

	// Test Connection
	$('.cc-test-connection-btn').on('click', function() {
		const $btn = $(this);
		const provider = $btn.data('provider');
		const $spinner = $btn.siblings('.cc-test-connection-spinner');
		const $result = $btn.siblings('.cc-test-connection-result');

		// Disable button and show spinner
		$btn.prop('disabled', true);
		$spinner.css('display', 'inline-block').addClass('is-active');
		$result.hide();

		// Update diagnostics panel to show testing state
		updateDiagnosticsStatus(provider, 'testing');

		// Make AJAX request
		$.ajax({
			url: chatcommerceAIAdmin.apiUrl + '/test-provider',
			method: 'POST',
			beforeSend: function(xhr) {
				xhr.setRequestHeader('X-WP-Nonce', chatcommerceAIAdmin.nonce);
			},
			data: JSON.stringify({ provider: provider }),
			contentType: 'application/json',
			success: function(response) {
				$result.html(
					'<div class="notice notice-success inline" style="margin: 0; padding: 8px 12px;">' +
					'<p style="margin: 0;">' +
					'<strong>✓ ' + response.message + '</strong><br>' +
					'<span style="color: #666;">Model: ' + response.model + ' | ' +
					'Latency: ' + response.latency_ms + 'ms | ' +
					'Provider: ' + provider + '</span>' +
					'</p>' +
					'</div>'
				).fadeIn();

				// Update diagnostics panel with success
				updateDiagnosticsStatus(provider, 'success', {
					message: response.message,
					model: response.model,
					latency: response.latency_ms,
					tokens: response.tokens
				});
			},
			error: function(xhr) {
				let errorMsg = 'Connection test failed.';
				let details = '';

				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;

					if (xhr.responseJSON.data) {
						const data = xhr.responseJSON.data;
						details = '<br><span style="color: #666; font-size: 12px;">';

						if (data.latency_ms) {
							details += 'Latency: ' + data.latency_ms + 'ms | ';
						}
						if (data.provider) {
							details += 'Provider: ' + data.provider;
						}

						details += '</span>';
					}
				}

				$result.html(
					'<div class="notice notice-error inline" style="margin: 0; padding: 8px 12px;">' +
					'<p style="margin: 0;">' +
					'<strong>✗ ' + errorMsg + '</strong>' +
					details +
					'</p>' +
					'</div>'
				).fadeIn();

				// Update diagnostics panel with error
				updateDiagnosticsStatus(provider, 'error', {
					message: errorMsg,
					latency: xhr.responseJSON?.data?.latency_ms
				});
			},
			complete: function() {
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active').hide();
			}
		});
	});

	// Function to update diagnostics panel
	function updateDiagnosticsStatus(provider, status, data) {
		const $card = $('#diagnostics-provider-' + provider);
		if (!$card.length) return;

		const $statusIndicator = $card.find('.status-indicator');
		const $statusText = $card.find('.status-text');
		const $details = $card.find('.status-details');
		const $timestamp = $card.find('.status-timestamp');

		// Update based on status
		if (status === 'testing') {
			$statusIndicator.css({
				'background': '#3b82f6',
				'animation': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite'
			});
			$statusText.html('⟳ Testing...').css('color', '#3b82f6');
			$details.html('<span style="color: #6b7280; font-size: 12px;">Running connection test...</span>');
			$timestamp.text('Just now');
		} else if (status === 'success') {
			$statusIndicator.css({
				'background': '#10b981',
				'animation': 'none'
			});
			$statusText.html('✓ Connected').css('color', '#10b981');

			let detailsHtml = '<span style="color: #6b7280; font-size: 12px;">';
			if (data.model) detailsHtml += '<strong>Model:</strong> ' + data.model + ' | ';
			if (data.latency) detailsHtml += '<strong>Latency:</strong> ' + data.latency + 'ms';
			if (data.tokens) detailsHtml += ' | <strong>Tokens:</strong> ' + data.tokens;
			detailsHtml += '</span>';

			$details.html(detailsHtml);
			$timestamp.text('Just now');
		} else if (status === 'error') {
			$statusIndicator.css({
				'background': '#ef4444',
				'animation': 'none'
			});
			$statusText.html('✗ Failed').css('color', '#ef4444');

			let detailsHtml = '<span style="color: #ef4444; font-size: 12px;">' + data.message;
			if (data.latency) detailsHtml += ' (Latency: ' + data.latency + 'ms)';
			detailsHtml += '</span>';

			$details.html(detailsHtml);
			$timestamp.text('Just now');
		}
	}
})(jQuery);
</script>

<?php
// Include diagnostics panel
$has_any_key = $has_openai_key || $has_hf_token;
if ( $has_any_key ) :
	// Fetch system status
	$status_response = wp_remote_get(
		rest_url( 'chatcommerce/v1/status' ),
		array(
			'headers' => array(
				'X-WP-Nonce' => wp_create_nonce( 'wp_rest' ),
			),
		)
	);

	$status_data = null;
	if ( ! is_wp_error( $status_response ) && 200 === wp_remote_retrieve_response_code( $status_response ) ) {
		$body = wp_remote_retrieve_body( $status_response );
		$data = json_decode( $body, true );
		if ( isset( $data['status'] ) ) {
			$status_data = $data['status'];
		}
	}
?>

<!-- System Diagnostics Panel -->
<div style="margin-top: 40px;">
	<h2 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--cc-border-default);">
		<?php esc_html_e( 'System Diagnostics', 'chatcommerce-ai' ); ?>
	</h2>

	<!-- Provider Connectivity Card -->
	<div class="cc-card" style="margin-bottom: 20px; max-width: 800px;">
		<h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: var(--cc-text-primary);">
			<?php esc_html_e( 'Provider Connectivity', 'chatcommerce-ai' ); ?>
		</h3>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
			<!-- OpenAI Status -->
			<div id="diagnostics-provider-openai" style="padding: 16px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 4px solid <?php echo $has_openai_key ? ( 'openai' === $current_provider ? '#3b82f6' : '#9ca3af' ) : '#d1d5db'; ?>;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
					<div class="status-indicator" style="width: 12px; height: 12px; border-radius: 50%; background: <?php echo $has_openai_key ? '#9ca3af' : '#d1d5db'; ?>; flex-shrink: 0;"></div>
					<div>
						<div style="font-size: 14px; font-weight: 600; color: var(--cc-text-primary); margin-bottom: 2px;">
							OpenAI
							<?php if ( 'openai' === $current_provider ) : ?>
								<span style="font-size: 11px; padding: 2px 6px; background: #3b82f6; color: white; border-radius: 4px; font-weight: 500; margin-left: 6px;">ACTIVE</span>
							<?php endif; ?>
						</div>
						<div class="status-text" style="font-size: 12px; color: var(--cc-text-secondary);">
							<?php echo $has_openai_key ? '○ Not tested' : '○ No API key configured'; ?>
						</div>
					</div>
				</div>
				<div class="status-details" style="font-size: 12px; color: var(--cc-text-secondary); margin: 8px 0; min-height: 18px;">
					<?php if ( $has_openai_key ) : ?>
						<span style="color: #6b7280;">Click "Test Connection" to verify</span>
					<?php else : ?>
						<span style="color: #ef4444;">Add your OpenAI API key above</span>
					<?php endif; ?>
				</div>
				<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--cc-border-default);">
					<div class="status-timestamp" style="font-size: 11px; color: var(--cc-text-tertiary);">
						<?php echo $has_openai_key ? '—' : 'Not configured'; ?>
					</div>
					<div style="font-size: 11px; color: var(--cc-text-secondary);">
						<?php echo esc_html( $settings['openai_model'] ?? 'gpt-4o-mini' ); ?>
					</div>
				</div>
			</div>

			<!-- Hugging Face Status -->
			<div id="diagnostics-provider-huggingface" style="padding: 16px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 4px solid <?php echo $has_hf_token ? ( 'huggingface' === $current_provider ? '#f59e0b' : '#9ca3af' ) : '#d1d5db'; ?>;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
					<div class="status-indicator" style="width: 12px; height: 12px; border-radius: 50%; background: <?php echo $has_hf_token ? '#9ca3af' : '#d1d5db'; ?>; flex-shrink: 0;"></div>
					<div>
						<div style="font-size: 14px; font-weight: 600; color: var(--cc-text-primary); margin-bottom: 2px;">
							Hugging Face
							<?php if ( 'huggingface' === $current_provider ) : ?>
								<span style="font-size: 11px; padding: 2px 6px; background: #f59e0b; color: white; border-radius: 4px; font-weight: 500; margin-left: 6px;">ACTIVE</span>
							<?php endif; ?>
						</div>
						<div class="status-text" style="font-size: 12px; color: var(--cc-text-secondary);">
							<?php echo $has_hf_token ? '○ Not tested' : '○ No access token configured'; ?>
						</div>
					</div>
				</div>
				<div class="status-details" style="font-size: 12px; color: var(--cc-text-secondary); margin: 8px 0; min-height: 18px;">
					<?php if ( $has_hf_token ) : ?>
						<span style="color: #6b7280;">Click "Test Connection" to verify</span>
					<?php else : ?>
						<span style="color: #ef4444;">Add your Hugging Face token above</span>
					<?php endif; ?>
				</div>
				<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--cc-border-default);">
					<div class="status-timestamp" style="font-size: 11px; color: var(--cc-text-tertiary);">
						<?php echo $has_hf_token ? '—' : 'Not configured'; ?>
					</div>
					<div style="font-size: 11px; color: var(--cc-text-secondary);">
						<?php
						$hf_model = $settings['hf_model'] ?? 'HuggingFaceH4/zephyr-7b-beta';
						$hf_model_parts = explode( '/', $hf_model );
						echo esc_html( end( $hf_model_parts ) );
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Health Status Card -->
	<div class="cc-card" style="margin-bottom: 20px; max-width: 800px;">
		<h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: var(--cc-text-primary);">
			<?php esc_html_e( 'System Status', 'chatcommerce-ai' ); ?>
		</h3>

		<?php if ( $status_data ) : ?>
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 16px;">
				<!-- Plugin Status -->
				<div style="padding: 12px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 3px solid <?php echo ! empty( $status_data['plugin']['enabled'] ) ? '#10b981' : '#6b7280'; ?>;">
					<div style="font-size: 12px; color: var(--cc-text-secondary); margin-bottom: 4px;">
						<?php esc_html_e( 'Plugin', 'chatcommerce-ai' ); ?>
					</div>
					<div style="font-size: 14px; font-weight: 600; color: var(--cc-text-primary);">
						<?php echo ! empty( $status_data['plugin']['enabled'] ) ? '✓ ' . esc_html__( 'Enabled', 'chatcommerce-ai' ) : '○ ' . esc_html__( 'Disabled', 'chatcommerce-ai' ); ?>
					</div>
				</div>

				<!-- REST API -->
				<div style="padding: 12px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 3px solid <?php echo ! empty( $status_data['system']['rest_api'] ) ? '#10b981' : '#ef4444'; ?>;">
					<div style="font-size: 12px; color: var(--cc-text-secondary); margin-bottom: 4px;">
						<?php esc_html_e( 'REST API', 'chatcommerce-ai' ); ?>
					</div>
					<div style="font-size: 14px; font-weight: 600; color: var(--cc-text-primary);">
						<?php echo ! empty( $status_data['system']['rest_api'] ) ? '✓ ' . esc_html__( 'Available', 'chatcommerce-ai' ) : '✗ ' . esc_html__( 'Unavailable', 'chatcommerce-ai' ); ?>
					</div>
				</div>

				<!-- Database -->
				<div style="padding: 12px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 3px solid #10b981;">
					<div style="font-size: 12px; color: var(--cc-text-secondary); margin-bottom: 4px;">
						<?php esc_html_e( 'Database', 'chatcommerce-ai' ); ?>
					</div>
					<div style="font-size: 14px; font-weight: 600; color: var(--cc-text-primary);">
						✓ <?php esc_html_e( 'Connected', 'chatcommerce-ai' ); ?>
					</div>
				</div>

				<!-- Plugin Version -->
				<div style="padding: 12px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 3px solid #8b5cf6;">
					<div style="font-size: 12px; color: var(--cc-text-secondary); margin-bottom: 4px;">
						<?php esc_html_e( 'Version', 'chatcommerce-ai' ); ?>
					</div>
					<div style="font-size: 14px; font-weight: 600; color: var(--cc-text-primary);">
						<?php echo esc_html( $status_data['plugin']['version'] ?? CHATCOMMERCE_AI_VERSION ); ?>
					</div>
				</div>
			</div>

			<!-- System Information -->
			<div style="padding: 12px; background: var(--cc-surface-base); border-radius: var(--cc-radius-md); font-size: 12px; color: var(--cc-text-secondary);">
				<strong><?php esc_html_e( 'Environment:', 'chatcommerce-ai' ); ?></strong>
				<?php
				printf(
					'PHP %s | WordPress %s | WooCommerce %s',
					esc_html( $status_data['system']['php_version'] ?? PHP_VERSION ),
					esc_html( $status_data['system']['wp_version'] ?? get_bloginfo( 'version' ) ),
					esc_html( $status_data['system']['wc_version'] ?? ( defined( 'WC_VERSION' ) ? WC_VERSION : 'N/A' ) )
				);
				?>
			</div>
		<?php else : ?>
			<div class="notice notice-warning inline" style="margin: 0;">
				<p><?php esc_html_e( 'Unable to fetch system status. Please check your REST API configuration.', 'chatcommerce-ai' ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<!-- Recent Errors Card -->
	<?php
	$recent_errors = get_option( 'chatcommerce_ai_recent_errors', array() );
	if ( ! empty( $recent_errors ) ) :
	?>
		<div class="cc-card" style="max-width: 800px;">
			<h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: var(--cc-text-primary);">
				<?php esc_html_e( 'Recent Errors', 'chatcommerce-ai' ); ?>
				<span style="font-size: 12px; font-weight: 400; color: var(--cc-text-secondary);">
					(<?php printf( esc_html__( 'Last %d', 'chatcommerce-ai' ), count( $recent_errors ) ); ?>)
				</span>
			</h3>

			<div style="display: flex; flex-direction: column; gap: 12px;">
				<?php foreach ( $recent_errors as $error ) : ?>
					<div style="padding: 12px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 3px solid #ef4444;">
						<div style="display: flex; justify-content: between; align-items: start; margin-bottom: 4px;">
							<div style="flex: 1;">
								<div style="font-size: 13px; font-weight: 500; color: var(--cc-text-primary); margin-bottom: 4px;">
									<?php echo esc_html( $error['message'] ?? 'Unknown error' ); ?>
								</div>
								<div style="font-size: 11px; color: var(--cc-text-secondary);">
									<strong><?php esc_html_e( 'Code:', 'chatcommerce-ai' ); ?></strong> <?php echo esc_html( $error['code'] ?? 'unknown' ); ?>
									|
									<strong><?php esc_html_e( 'Request ID:', 'chatcommerce-ai' ); ?></strong> <?php echo esc_html( $error['request_id'] ?? 'N/A' ); ?>
								</div>
							</div>
							<div style="font-size: 11px; color: var(--cc-text-tertiary); white-space: nowrap; margin-left: 16px;">
								<?php
								if ( isset( $error['timestamp'] ) ) {
									$timestamp = strtotime( $error['timestamp'] );
									echo esc_html( human_time_diff( $timestamp, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'chatcommerce-ai' ) );
								}
								?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<p style="margin: 12px 0 0 0; font-size: 12px; color: var(--cc-text-secondary);">
				<?php esc_html_e( 'Errors are logged for troubleshooting. Use the Request ID to correlate with server logs.', 'chatcommerce-ai' ); ?>
			</p>
		</div>
	<?php endif; ?>
</div>
<?php endif; ?>
