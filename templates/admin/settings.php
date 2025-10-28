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

<div class="wrap chatcommerce-ai-settings">
	<h1><?php esc_html_e( 'ChatCommerce AI Settings', 'chatcommerce-ai' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
			<a
				href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=' . $tab_key ) ); ?>"
				class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $tab_label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" action="">
		<?php wp_nonce_field( 'chatcommerce_ai_settings' ); ?>

		<div class="tab-content">
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

		<?php submit_button( __( 'Save Settings', 'chatcommerce-ai' ), 'primary', 'chatcommerce_ai_settings_submit' ); ?>
	</form>
</div>

<style>
.chatcommerce-ai-settings .tab-content {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-top: none;
	padding: 20px;
	margin: 0 0 20px 0;
}

.chatcommerce-ai-settings table.form-table {
	margin-top: 20px;
}

.chatcommerce-ai-settings .setting-description {
	color: #646970;
	font-size: 13px;
	margin: 5px 0 0 0;
}

.chatcommerce-ai-settings .color-picker-group {
	display: flex;
	gap: 10px;
	align-items: center;
}

.chatcommerce-ai-settings input[type="color"] {
	height: 40px;
	width: 80px;
	border: 1px solid #8c8f94;
	border-radius: 4px;
	cursor: pointer;
}

.chatcommerce-ai-settings .api-key-input {
	font-family: monospace;
	width: 400px;
}
</style>
