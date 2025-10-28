<?php
/**
 * Admin Dashboard Template
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Get stats.
$sessions_table = $wpdb->prefix . 'chatcommerce_sessions';
$messages_table = $wpdb->prefix . 'chatcommerce_messages';
$leads_table    = $wpdb->prefix . 'chatcommerce_leads';
$feedback_table = $wpdb->prefix . 'chatcommerce_feedback';

$total_sessions    = $wpdb->get_var( "SELECT COUNT(*) FROM {$sessions_table}" );
$total_messages    = $wpdb->get_var( "SELECT COUNT(*) FROM {$messages_table}" );
$total_leads       = $wpdb->get_var( "SELECT COUNT(*) FROM {$leads_table}" );
$positive_feedback = $wpdb->get_var( "SELECT COUNT(*) FROM {$feedback_table} WHERE rating = 1" );
$total_feedback    = $wpdb->get_var( "SELECT COUNT(*) FROM {$feedback_table}" );

$settings = get_option( 'chatcommerce_ai_settings', array() );
$is_enabled = ! empty( $settings['enabled'] );
$has_api_key = ! empty( $settings['openai_api_key'] );

?>

<div class="wrap chatcommerce-ai-dashboard">
	<h1><?php esc_html_e( 'ChatCommerce AI Dashboard', 'chatcommerce-ai' ); ?></h1>

	<?php if ( ! $has_api_key ) : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				printf(
					/* translators: %s: settings URL */
					__( 'Please <a href="%s">configure your OpenAI API key</a> to start using ChatCommerce AI.', 'chatcommerce-ai' ),
					esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=ai' ) )
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="chatcommerce-ai-status-card">
		<h2><?php esc_html_e( 'Status', 'chatcommerce-ai' ); ?></h2>
		<p>
			<strong><?php esc_html_e( 'Chatbot Status:', 'chatcommerce-ai' ); ?></strong>
			<span class="status-badge <?php echo $is_enabled ? 'status-enabled' : 'status-disabled'; ?>">
				<?php echo $is_enabled ? esc_html__( 'Enabled', 'chatcommerce-ai' ) : esc_html__( 'Disabled', 'chatcommerce-ai' ); ?>
			</span>
		</p>
		<p>
			<strong><?php esc_html_e( 'OpenAI API:', 'chatcommerce-ai' ); ?></strong>
			<span class="status-badge <?php echo $has_api_key ? 'status-enabled' : 'status-disabled'; ?>">
				<?php echo $has_api_key ? esc_html__( 'Configured', 'chatcommerce-ai' ) : esc_html__( 'Not Configured', 'chatcommerce-ai' ); ?>
			</span>
		</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Manage Settings', 'chatcommerce-ai' ); ?>
			</a>
		</p>
	</div>

	<div class="chatcommerce-ai-stats-grid">
		<div class="stat-card">
			<h3><?php esc_html_e( 'Total Sessions', 'chatcommerce-ai' ); ?></h3>
			<div class="stat-value"><?php echo number_format_i18n( $total_sessions ); ?></div>
		</div>

		<div class="stat-card">
			<h3><?php esc_html_e( 'Total Messages', 'chatcommerce-ai' ); ?></h3>
			<div class="stat-value"><?php echo number_format_i18n( $total_messages ); ?></div>
		</div>

		<div class="stat-card">
			<h3><?php esc_html_e( 'Leads Captured', 'chatcommerce-ai' ); ?></h3>
			<div class="stat-value"><?php echo number_format_i18n( $total_leads ); ?></div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-leads' ) ); ?>">
				<?php esc_html_e( 'View Leads', 'chatcommerce-ai' ); ?>
			</a>
		</div>

		<div class="stat-card">
			<h3><?php esc_html_e( 'Customer Satisfaction', 'chatcommerce-ai' ); ?></h3>
			<div class="stat-value">
				<?php
				if ( $total_feedback > 0 ) {
					echo number_format_i18n( ( $positive_feedback / $total_feedback ) * 100, 1 );
					echo '%';
				} else {
					echo '—';
				}
				?>
			</div>
			<p class="stat-meta">
				<?php
				printf(
					/* translators: 1: positive count, 2: total count */
					__( '%1$d of %2$d positive', 'chatcommerce-ai' ),
					number_format_i18n( $positive_feedback ),
					number_format_i18n( $total_feedback )
				);
				?>
			</p>
		</div>
	</div>

	<div class="chatcommerce-ai-quick-actions">
		<h2><?php esc_html_e( 'Quick Actions', 'chatcommerce-ai' ); ?></h2>
		<div class="quick-actions-grid">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=instructions' ) ); ?>" class="quick-action-card">
				<span class="dashicons dashicons-edit-large"></span>
				<h3><?php esc_html_e( 'Edit Instructions', 'chatcommerce-ai' ); ?></h3>
				<p><?php esc_html_e( 'Customize AI behavior', 'chatcommerce-ai' ); ?></p>
			</a>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-sync' ) ); ?>" class="quick-action-card">
				<span class="dashicons dashicons-update"></span>
				<h3><?php esc_html_e( 'Sync Content', 'chatcommerce-ai' ); ?></h3>
				<p><?php esc_html_e( 'Update product catalog', 'chatcommerce-ai' ); ?></p>
			</a>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-conversations' ) ); ?>" class="quick-action-card">
				<span class="dashicons dashicons-format-chat"></span>
				<h3><?php esc_html_e( 'View Conversations', 'chatcommerce-ai' ); ?></h3>
				<p><?php esc_html_e( 'Review chat history', 'chatcommerce-ai' ); ?></p>
			</a>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings' ) ); ?>" class="quick-action-card">
				<span class="dashicons dashicons-admin-settings"></span>
				<h3><?php esc_html_e( 'Settings', 'chatcommerce-ai' ); ?></h3>
				<p><?php esc_html_e( 'Configure plugin', 'chatcommerce-ai' ); ?></p>
			</a>
		</div>
	</div>
</div>

<style>
.chatcommerce-ai-dashboard .chatcommerce-ai-status-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 20px;
	margin: 20px 0;
}

.status-badge {
	display: inline-block;
	padding: 4px 12px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
}

.status-enabled {
	background: #00a32a;
	color: #fff;
}

.status-disabled {
	background: #dba617;
	color: #fff;
}

.chatcommerce-ai-stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
	margin: 20px 0;
}

.stat-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 20px;
	text-align: center;
}

.stat-card h3 {
	margin: 0 0 10px 0;
	font-size: 14px;
	color: #646970;
	font-weight: 400;
}

.stat-value {
	font-size: 32px;
	font-weight: 700;
	color: #1d2327;
	margin: 10px 0;
}

.stat-meta {
	font-size: 12px;
	color: #646970;
	margin: 5px 0 0 0;
}

.quick-actions-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
	margin: 20px 0;
}

.quick-action-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 20px;
	text-align: center;
	text-decoration: none;
	transition: all 0.2s;
}

.quick-action-card:hover {
	border-color: #2271b1;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quick-action-card .dashicons {
	font-size: 48px;
	width: 48px;
	height: 48px;
	color: #2271b1;
}

.quick-action-card h3 {
	margin: 10px 0 5px 0;
	font-size: 16px;
	color: #1d2327;
}

.quick-action-card p {
	margin: 0;
	font-size: 13px;
	color: #646970;
}
</style>
