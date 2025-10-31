<?php
/**
 * Admin Settings Template
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = get_option( 'chatcommerce_ai_settings', array() );
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

// Handle form submission.
if ( isset( $_POST['chatcommerce_ai_settings_submit'] ) ) {
	check_admin_referer( 'chatcommerce_ai_settings' );

	$input = $_POST['chatcommerce_ai'] ?? array();
	$current_settings = get_option( 'chatcommerce_ai_settings', array() );

	// Manually handle encryption for API keys/tokens to preserve existing values when not changed.
	$sanitized = $current_settings; // Start with current settings

	// General settings.
	$sanitized['enabled']         = ! empty( $input['enabled'] );
	$sanitized['position']        = sanitize_text_field( $input['position'] ?? 'bottom-right' );
	$sanitized['primary_color']   = sanitize_hex_color( $input['primary_color'] ?? '#0073aa' );
	$sanitized['bg_color']        = sanitize_hex_color( $input['bg_color'] ?? '#ffffff' );
	$sanitized['text_color']      = sanitize_hex_color( $input['text_color'] ?? '#000000' );
	$sanitized['welcome_message'] = sanitize_textarea_field( $input['welcome_message'] ?? '' );
	$sanitized['brand_logo']      = esc_url_raw( $input['brand_logo'] ?? '' );

	// AI Provider.
	$sanitized['ai_provider'] = sanitize_text_field( $input['ai_provider'] ?? 'openai' );
	if ( ! in_array( $sanitized['ai_provider'], array( 'openai', 'huggingface' ), true ) ) {
		$sanitized['ai_provider'] = 'openai';
	}

	// OpenAI - check if key was changed (not placeholder dots).
	if ( ! empty( $input['openai_api_key'] ) && strpos( $input['openai_api_key'], '•' ) === false ) {
		$sanitized['openai_api_key'] = \ChatCommerceAI\Admin\AdminController::encrypt_api_key( sanitize_text_field( $input['openai_api_key'] ) );
	}
	$sanitized['openai_model'] = sanitize_text_field( $input['openai_model'] ?? 'gpt-4o-mini' );

	// Hugging Face - check if token was changed (not placeholder dots).
	if ( ! empty( $input['hf_access_token'] ) && strpos( $input['hf_access_token'], '•' ) === false ) {
		$sanitized['hf_access_token'] = \ChatCommerceAI\Admin\AdminController::encrypt_api_key( sanitize_text_field( $input['hf_access_token'] ) );
	}
	$sanitized['hf_model'] = sanitize_text_field( $input['hf_model'] ?? 'mistralai/Mistral-7B-Instruct-v0.2' );

	// Model parameters.
	$sanitized['temperature'] = floatval( $input['temperature'] ?? 0.7 );
	$sanitized['max_tokens']  = intval( $input['max_tokens'] ?? 500 );

	// Other settings.
	$sanitized['function_calling']      = ! empty( $input['function_calling'] );
	$sanitized['safety_filters']        = ! empty( $input['safety_filters'] );
	$sanitized['sync_post_types']       = isset( $input['sync_post_types'] ) && is_array( $input['sync_post_types'] ) ? array_map( 'sanitize_text_field', $input['sync_post_types'] ) : array( 'post', 'page', 'product' );
	$sanitized['sync_schedule']         = sanitize_text_field( $input['sync_schedule'] ?? 'hourly' );
	$sanitized['lead_capture_enabled']  = ! empty( $input['lead_capture_enabled'] );
	$sanitized['lead_fields']           = isset( $input['lead_fields'] ) && is_array( $input['lead_fields'] ) ? array_map( 'sanitize_text_field', $input['lead_fields'] ) : array( 'name', 'email' );
	$sanitized['feedback_enabled']      = ! empty( $input['feedback_enabled'] );
	$sanitized['telemetry_enabled']     = ! empty( $input['telemetry_enabled'] );
	$sanitized['data_retention_days']   = intval( $input['data_retention_days'] ?? 30 );

	update_option( 'chatcommerce_ai_settings', $sanitized );

	// Modern success message
	?>
	<div class="cc-success-message" style="position: fixed; top: 32px; right: 32px; z-index: 999999; animation: slideInRight 0.4s ease-out;">
		<div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3), 0 4px 12px rgba(0, 0, 0, 0.1); display: flex; align-items: center; gap: 12px; min-width: 320px;">
			<div style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</div>
			<div style="flex: 1;">
				<div style="font-size: 16px; font-weight: 600; margin-bottom: 2px;">
					<?php esc_html_e( 'Settings Saved!', 'chatcommerce-ai' ); ?>
				</div>
				<div style="font-size: 13px; opacity: 0.95;">
					<?php esc_html_e( 'Your changes have been successfully saved.', 'chatcommerce-ai' ); ?>
				</div>
			</div>
			<button type="button" onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; opacity: 0.8; cursor: pointer; padding: 4px; margin: -4px; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
	</div>
	<style>
		@keyframes slideInRight {
			from {
				transform: translateX(400px);
				opacity: 0;
			}
			to {
				transform: translateX(0);
				opacity: 1;
			}
		}
		@keyframes fadeOut {
			to {
				opacity: 0;
				transform: translateX(400px);
			}
		}
		.cc-success-message {
			animation: slideInRight 0.4s ease-out, fadeOut 0.3s ease-in 4.7s forwards;
		}
	</style>
	<script>
		setTimeout(function() {
			const successMsg = document.querySelector('.cc-success-message');
			if (successMsg) {
				setTimeout(function() {
					successMsg.remove();
				}, 5000);
			}
		}, 100);
	</script>
	<?php
	$settings = get_option( 'chatcommerce_ai_settings', array() );
}

$tabs = array(
	'general'      => __( 'General', 'chatcommerce-ai' ),
	'ai'           => __( 'AI Settings', 'chatcommerce-ai' ),
	'knowledge'    => __( 'Knowledge & Sync', 'chatcommerce-ai' ),
	'instructions' => __( 'Instructions', 'chatcommerce-ai' ),
	'lead'         => __( 'Lead Capture', 'chatcommerce-ai' ),
	'feedback'     => __( 'Feedback', 'chatcommerce-ai' ),
	'privacy'      => __( 'Privacy', 'chatcommerce-ai' ),
);

?>

<div class="wrap chatcommerce-ai-wrap chatcommerce-ai-settings">
	<!-- Page Header -->
	<div class="cc-page-header">
		<div>
			<h1 class="cc-page-title"><?php esc_html_e( 'Settings', 'chatcommerce-ai' ); ?></h1>
			<p class="cc-text-small" style="margin: var(--cc-space-2) 0 0 0;">
				<?php esc_html_e( 'Configure your ChatCommerce AI chatbot settings', 'chatcommerce-ai' ); ?>
			</p>
		</div>
		<div class="cc-page-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai' ) ); ?>" class="cc-button cc-button-ghost">
				<?php esc_html_e( 'Back to Dashboard', 'chatcommerce-ai' ); ?>
			</a>
		</div>
	</div>

	<!-- Tabs Navigation -->
	<nav class="nav-tab-wrapper" role="tablist">
		<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
			<a
				href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=' . $tab_key ) ); ?>"
				class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>"
				role="tab"
				aria-selected="<?php echo $active_tab === $tab_key ? 'true' : 'false'; ?>"
				aria-controls="settings-<?php echo esc_attr( $tab_key ); ?>">
				<?php echo esc_html( $tab_label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<!-- Settings Form -->
	<form method="post" action="">
		<?php wp_nonce_field( 'chatcommerce_ai_settings' ); ?>

		<div class="tab-content" id="settings-<?php echo esc_attr( $active_tab ); ?>" role="tabpanel">
			<?php
			switch ( $active_tab ) {
				case 'general':
					require CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/settings/general.php';
					break;
				case 'ai':
					require CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/settings/ai.php';
					break;
				case 'knowledge':
					require CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/settings/knowledge.php';
					break;
				case 'instructions':
					require CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/settings/instructions.php';
					break;
				case 'lead':
					require CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/settings/lead.php';
					break;
				case 'feedback':
					require CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/settings/feedback.php';
					break;
				case 'privacy':
					require CHATCOMMERCE_AI_PLUGIN_DIR . 'templates/admin/settings/privacy.php';
					break;
			}
			?>
		</div>

		<div class="cc-card-footer" style="margin-top: var(--cc-space-6); padding: var(--cc-space-6); background: var(--cc-surface-raised); border: 1px solid var(--cc-border-default); border-radius: var(--cc-radius-xl);">
			<?php submit_button( __( 'Save Settings', 'chatcommerce-ai' ), 'primary', 'chatcommerce_ai_settings_submit', false ); ?>
			<button type="button" class="button cc-button cc-button-ghost" onclick="window.location.reload();">
				<?php esc_html_e( 'Reset', 'chatcommerce-ai' ); ?>
			</button>
		</div>
	</form>
</div>
