<?php
/**
 * Admin Dashboard Template
 * Redesigned with Modern Classic Design System
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

// Calculate CSAT percentage
$csat_percentage = $total_feedback > 0 ? ( $positive_feedback / $total_feedback ) * 100 : 0;

// Get average response time (mock for now - can be calculated from messages table later)
$avg_response_time = '2.3s';

$settings = get_option( 'chatcommerce_ai_settings', array() );
$is_enabled = ! empty( $settings['enabled'] );
$has_api_key = ! empty( $settings['openai_api_key'] );

?>

<div class="wrap chatcommerce-ai-wrap chatcommerce-ai-dashboard">
	<!-- Page Header -->
	<div class="cc-page-header">
		<h1 class="cc-page-title"><?php esc_html_e( 'Dashboard', 'chatcommerce-ai' ); ?></h1>
		<div class="cc-page-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings' ) ); ?>" class="cc-button cc-button-secondary">
				<span class="dashicons dashicons-admin-settings" style="margin-top: 3px;"></span>
				<?php esc_html_e( 'Settings', 'chatcommerce-ai' ); ?>
			</a>
		</div>
	</div>

	<!-- Configuration Alert -->
	<?php if ( ! $has_api_key ) : ?>
		<div class="cc-alert cc-alert-warning">
			<svg class="cc-alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
			</svg>
			<div class="cc-alert-content">
				<div class="cc-alert-title"><?php esc_html_e( 'Configuration Required', 'chatcommerce-ai' ); ?></div>
				<div class="cc-alert-description">
					<?php
					printf(
						/* translators: %s: settings URL */
						__( 'Please <a href="%s" style="font-weight: 600; text-decoration: underline;">configure your OpenAI API key</a> to start using ChatCommerce AI.', 'chatcommerce-ai' ),
						esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=ai' ) )
					);
					?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- Status Card -->
	<div class="cc-card cc-mb-8">
		<div class="cc-flex cc-items-center cc-justify-between">
			<div>
				<h2 class="cc-heading-4"><?php esc_html_e( 'System Status', 'chatcommerce-ai' ); ?></h2>
				<p class="cc-text-small" style="margin: 0;">
					<?php esc_html_e( 'Monitor the health and configuration of your chatbot', 'chatcommerce-ai' ); ?>
				</p>
			</div>
			<div class="cc-flex cc-gap-4">
				<div style="text-align: center;">
					<div class="cc-text-small cc-mb-2"><?php esc_html_e( 'Chatbot', 'chatcommerce-ai' ); ?></div>
					<span class="cc-badge <?php echo $is_enabled ? 'cc-badge-success' : 'cc-badge-warning'; ?>">
						<?php echo $is_enabled ? esc_html__( 'Active', 'chatcommerce-ai' ) : esc_html__( 'Inactive', 'chatcommerce-ai' ); ?>
					</span>
				</div>
				<div style="text-align: center;">
					<div class="cc-text-small cc-mb-2"><?php esc_html_e( 'OpenAI API', 'chatcommerce-ai' ); ?></div>
					<span class="cc-badge <?php echo $has_api_key ? 'cc-badge-success' : 'cc-badge-error'; ?>">
						<?php echo $has_api_key ? esc_html__( 'Connected', 'chatcommerce-ai' ) : esc_html__( 'Not Connected', 'chatcommerce-ai' ); ?>
					</span>
				</div>
			</div>
		</div>
	</div>

	<!-- Key Performance Indicators -->
	<h2 class="cc-heading-3 cc-mb-6"><?php esc_html_e( 'Key Metrics', 'chatcommerce-ai' ); ?></h2>
	<div class="cc-grid cc-grid-cols-1 cc-grid-md-cols-2 cc-grid-lg-cols-4 cc-mb-8">
		<!-- Total Sessions -->
		<div class="cc-stat-card cc-stat-card-hover">
			<svg class="cc-stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
			</svg>
			<span class="cc-stat-label"><?php esc_html_e( 'Total Sessions', 'chatcommerce-ai' ); ?></span>
			<span class="cc-stat-value"><?php echo number_format_i18n( $total_sessions ); ?></span>
			<span class="cc-stat-meta"><?php esc_html_e( 'All-time conversations', 'chatcommerce-ai' ); ?></span>
		</div>

		<!-- Total Messages -->
		<div class="cc-stat-card cc-stat-card-hover">
			<svg class="cc-stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
			</svg>
			<span class="cc-stat-label"><?php esc_html_e( 'Messages', 'chatcommerce-ai' ); ?></span>
			<span class="cc-stat-value"><?php echo number_format_i18n( $total_messages ); ?></span>
			<span class="cc-stat-meta"><?php esc_html_e( 'Total messages exchanged', 'chatcommerce-ai' ); ?></span>
		</div>

		<!-- Leads Captured -->
		<div class="cc-stat-card cc-stat-card-hover">
			<svg class="cc-stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
			</svg>
			<span class="cc-stat-label"><?php esc_html_e( 'Leads', 'chatcommerce-ai' ); ?></span>
			<span class="cc-stat-value"><?php echo number_format_i18n( $total_leads ); ?></span>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-leads' ) ); ?>" class="cc-stat-meta" style="color: var(--cc-color-primary-600); text-decoration: none; font-weight: 500;">
				<?php esc_html_e( 'View all leads →', 'chatcommerce-ai' ); ?>
			</a>
		</div>

		<!-- Customer Satisfaction -->
		<div class="cc-stat-card cc-stat-card-hover">
			<svg class="cc-stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span class="cc-stat-label"><?php esc_html_e( 'CSAT', 'chatcommerce-ai' ); ?></span>
			<span class="cc-stat-value">
				<?php
				if ( $total_feedback > 0 ) {
					echo number_format_i18n( $csat_percentage, 1 ) . '%';
				} else {
					echo '—';
				}
				?>
			</span>
			<span class="cc-stat-meta">
				<?php
				if ( $total_feedback > 0 ) {
					printf(
						/* translators: 1: positive count, 2: total count */
						esc_html__( '%1$d of %2$d positive', 'chatcommerce-ai' ),
						number_format_i18n( $positive_feedback ),
						number_format_i18n( $total_feedback )
					);
				} else {
					esc_html_e( 'No feedback yet', 'chatcommerce-ai' );
				}
				?>
			</span>
		</div>
	</div>

	<!-- Quick Actions -->
	<h2 class="cc-heading-3 cc-mb-6"><?php esc_html_e( 'Quick Actions', 'chatcommerce-ai' ); ?></h2>
	<div class="cc-grid cc-grid-cols-1 cc-grid-md-cols-2 cc-grid-lg-cols-4">
		<!-- Edit Instructions -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=instructions' ) ); ?>" class="cc-card cc-card-hover" style="text-decoration: none;">
			<div style="text-align: center;">
				<span class="dashicons dashicons-edit-large" style="font-size: 48px; width: 48px; height: 48px; color: var(--cc-color-primary-600); opacity: 0.9;"></span>
				<h3 class="cc-heading-5" style="margin: var(--cc-space-3) 0 var(--cc-space-2) 0;"><?php esc_html_e( 'Edit Instructions', 'chatcommerce-ai' ); ?></h3>
				<p class="cc-text-small" style="margin: 0;"><?php esc_html_e( 'Customize AI behavior and responses', 'chatcommerce-ai' ); ?></p>
			</div>
		</a>

		<!-- Sync Content -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-sync' ) ); ?>" class="cc-card cc-card-hover" style="text-decoration: none;">
			<div style="text-align: center;">
				<span class="dashicons dashicons-update" style="font-size: 48px; width: 48px; height: 48px; color: var(--cc-color-primary-600); opacity: 0.9;"></span>
				<h3 class="cc-heading-5" style="margin: var(--cc-space-3) 0 var(--cc-space-2) 0;"><?php esc_html_e( 'Sync Content', 'chatcommerce-ai' ); ?></h3>
				<p class="cc-text-small" style="margin: 0;"><?php esc_html_e( 'Update product catalog and knowledge base', 'chatcommerce-ai' ); ?></p>
			</div>
		</a>

		<!-- View Conversations -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-conversations' ) ); ?>" class="cc-card cc-card-hover" style="text-decoration: none;">
			<div style="text-align: center;">
				<span class="dashicons dashicons-format-chat" style="font-size: 48px; width: 48px; height: 48px; color: var(--cc-color-primary-600); opacity: 0.9;"></span>
				<h3 class="cc-heading-5" style="margin: var(--cc-space-3) 0 var(--cc-space-2) 0;"><?php esc_html_e( 'Conversations', 'chatcommerce-ai' ); ?></h3>
				<p class="cc-text-small" style="margin: 0;"><?php esc_html_e( 'Review chat history and transcripts', 'chatcommerce-ai' ); ?></p>
			</div>
		</a>

		<!-- View Leads -->
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-leads' ) ); ?>" class="cc-card cc-card-hover" style="text-decoration: none;">
			<div style="text-align: center;">
				<span class="dashicons dashicons-admin-users" style="font-size: 48px; width: 48px; height: 48px; color: var(--cc-color-primary-600); opacity: 0.9;"></span>
				<h3 class="cc-heading-5" style="margin: var(--cc-space-3) 0 var(--cc-space-2) 0;"><?php esc_html_e( 'Leads', 'chatcommerce-ai' ); ?></h3>
				<p class="cc-text-small" style="margin: 0;"><?php esc_html_e( 'Manage captured lead information', 'chatcommerce-ai' ); ?></p>
			</div>
		</a>
	</div>
</div>
