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
			<div class="cc-api-key-wrapper">
				<?php if ( $has_api_key ) : ?>
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
			<?php if ( $has_api_key ) : ?>
				<p class="description">
					<?php esc_html_e( 'API key is set and encrypted. Click the eye icon to view, or enter a new key to replace it.', 'chatcommerce-ai' ); ?>
				</p>
			<?php else : ?>
				<p class="description">
					<?php
					printf(
						/* translators: %s: OpenAI API keys URL */
						__( 'Get your API key from <a href="%s" target="_blank">OpenAI Platform</a>.', 'chatcommerce-ai' ),
						'https://platform.openai.com/api-keys'
					);
					?>
				</p>
			<?php endif; ?>

			<?php if ( $has_api_key ) : ?>
				<!-- Test Connection Button -->
				<div style="margin-top: 12px;">
					<button
						type="button"
						id="cc-test-connection-btn"
						class="button button-secondary"
						style="display: inline-flex; align-items: center; gap: 8px;"
					>
						<span class="dashicons dashicons-admin-plugins" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
						<?php esc_html_e( 'Test Connection', 'chatcommerce-ai' ); ?>
					</button>
					<span id="cc-test-connection-spinner" class="spinner" style="float: none; margin: 0 0 0 8px; display: none;"></span>
				</div>

				<!-- Test Result Display -->
				<div id="cc-test-connection-result" style="margin-top: 12px; display: none;"></div>

				<script>
				(function($) {
					$('#cc-test-connection-btn').on('click', function() {
						const $btn = $(this);
						const $spinner = $('#cc-test-connection-spinner');
						const $result = $('#cc-test-connection-result');

						// Disable button and show spinner
						$btn.prop('disabled', true);
						$spinner.css('display', 'inline-block').addClass('is-active');
						$result.hide();

						// Make AJAX request
						$.ajax({
							url: chatcommerceAIAdmin.apiUrl + '/test-connection',
							method: 'POST',
							beforeSend: function(xhr) {
								xhr.setRequestHeader('X-WP-Nonce', chatcommerceAIAdmin.nonce);
							},
							success: function(response) {
								$result.html(
									'<div class="notice notice-success inline" style="margin: 0; padding: 8px 12px;">' +
									'<p style="margin: 0;">' +
									'<strong>✓ ' + response.message + '</strong><br>' +
									'<span style="color: #666;">Model: ' + response.model + ' | ' +
									'Latency: ' + response.latency_ms + 'ms | ' +
									'Request ID: ' + response.request_id + '</span>' +
									'</p>' +
									'</div>'
								).fadeIn();
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
										if (data.request_id) {
											details += 'Request ID: ' + data.request_id;
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
							},
							complete: function() {
								$btn.prop('disabled', false);
								$spinner.removeClass('is-active').hide();
							}
						});
					});
				})(jQuery);
				</script>
			<?php endif; ?>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="openai_model"><?php esc_html_e( 'AI Model', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[openai_model]" id="openai_model" class="cc-form-select" style="max-width: 400px;">
				<option value="gpt-4o-mini" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-4o-mini' ); ?>>
					⚡ GPT-4o Mini (Recommended - Fast & Affordable)
				</option>
				<option value="gpt-4-turbo-preview" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-4-turbo-preview' ); ?>>
					🚀 GPT-4 Turbo
				</option>
				<option value="gpt-4o" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-4o' ); ?>>
					✨ GPT-4o (Latest)
				</option>
				<option value="gpt-4" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-4' ); ?>>
					🎯 GPT-4
				</option>
				<option value="gpt-3.5-turbo" <?php selected( $settings['openai_model'] ?? 'gpt-4o-mini', 'gpt-3.5-turbo' ); ?>>
					📦 GPT-3.5 Turbo (Legacy)
				</option>
			</select>
			<p class="description">
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

<?php if ( $has_api_key ) : ?>
	<!-- System Diagnostics Panel -->
	<div style="margin-top: 40px;">
		<h2 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--cc-border-default);">
			<?php esc_html_e( 'System Diagnostics', 'chatcommerce-ai' ); ?>
		</h2>

		<?php
		// Fetch system status.
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

		<!-- Health Status Card -->
		<div class="cc-card" style="margin-bottom: 20px; max-width: 800px;">
			<h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 600; color: var(--cc-text-primary);">
				<?php esc_html_e( 'Health Status', 'chatcommerce-ai' ); ?>
			</h3>

			<?php if ( $status_data ) : ?>
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
					<!-- Plugin Status -->
					<div style="padding: 12px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 3px solid <?php echo ! empty( $status_data['plugin']['enabled'] ) ? '#10b981' : '#6b7280'; ?>;">
						<div style="font-size: 12px; color: var(--cc-text-secondary); margin-bottom: 4px;">
							<?php esc_html_e( 'Plugin Status', 'chatcommerce-ai' ); ?>
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

					<!-- OpenAI Connection -->
					<div style="padding: 12px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 3px solid <?php
						if ( isset( $status_data['openai']['connectivity']['status'] ) ) {
							echo 'connected' === $status_data['openai']['connectivity']['status'] ? '#10b981' : '#ef4444';
						} else {
							echo '#6b7280';
						}
					?>;">
						<div style="font-size: 12px; color: var(--cc-text-secondary); margin-bottom: 4px;">
							<?php esc_html_e( 'OpenAI Status', 'chatcommerce-ai' ); ?>
						</div>
						<div style="font-size: 14px; font-weight: 600; color: var(--cc-text-primary);">
							<?php
							if ( isset( $status_data['openai']['connectivity']['status'] ) ) {
								if ( 'connected' === $status_data['openai']['connectivity']['status'] ) {
									printf(
										'✓ %s (%dms)',
										esc_html__( 'Connected', 'chatcommerce-ai' ),
										intval( $status_data['openai']['connectivity']['latency_ms'] ?? 0 )
									);
								} else {
									echo '✗ ' . esc_html( $status_data['openai']['connectivity']['message'] ?? __( 'Error', 'chatcommerce-ai' ) );
								}
							} else {
								echo '○ ' . esc_html__( 'Not configured', 'chatcommerce-ai' );
							}
							?>
						</div>
					</div>

					<!-- Plugin Version -->
					<div style="padding: 12px; background: var(--cc-surface-raised); border-radius: var(--cc-radius-md); border-left: 3px solid #3b82f6;">
						<div style="font-size: 12px; color: var(--cc-text-secondary); margin-bottom: 4px;">
							<?php esc_html_e( 'Version', 'chatcommerce-ai' ); ?>
						</div>
						<div style="font-size: 14px; font-weight: 600; color: var(--cc-text-primary);">
							<?php echo esc_html( $status_data['plugin']['version'] ?? 'Unknown' ); ?>
						</div>
					</div>
				</div>

				<!-- System Information -->
				<div style="padding: 12px; background: var(--cc-surface-base); border-radius: var(--cc-radius-md); font-size: 12px; color: var(--cc-text-secondary);">
					<strong><?php esc_html_e( 'System:', 'chatcommerce-ai' ); ?></strong>
					<?php
					printf(
						'PHP %s | WordPress %s | WooCommerce %s',
						esc_html( $status_data['system']['php_version'] ?? 'Unknown' ),
						esc_html( $status_data['system']['wp_version'] ?? 'Unknown' ),
						esc_html( $status_data['system']['wc_version'] ?? 'N/A' )
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
