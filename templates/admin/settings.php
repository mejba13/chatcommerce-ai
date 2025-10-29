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

	$updated_settings = array_merge( $settings, $_POST['chatcommerce_ai'] ?? array() );
	update_option( 'chatcommerce_ai_settings', $updated_settings );

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'chatcommerce-ai' ) . '</p></div>';
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
