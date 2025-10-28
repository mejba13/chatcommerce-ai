<?php
/**
 * Sync Dashboard Template
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$sync_table = $wpdb->prefix . 'chatcommerce_sync_index';

// Get sync stats.
$total_indexed = $wpdb->get_var( "SELECT COUNT(*) FROM {$sync_table}" );
$products      = $wpdb->get_var( "SELECT COUNT(*) FROM {$sync_table} WHERE doc_type = 'product'" );
$pages         = $wpdb->get_var( "SELECT COUNT(*) FROM {$sync_table} WHERE doc_type = 'page'" );
$posts         = $wpdb->get_var( "SELECT COUNT(*) FROM {$sync_table} WHERE doc_type = 'post'" );
$last_synced   = $wpdb->get_var( "SELECT MAX(last_synced) FROM {$sync_table}" );

// Handle manual sync trigger.
if ( isset( $_POST['trigger_sync'] ) ) {
	check_admin_referer( 'chatcommerce_ai_sync' );

	// Trigger sync action.
	do_action( 'chatcommerce_ai_trigger_sync' );

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Full sync initiated. This may take a few minutes.', 'chatcommerce-ai' ) . '</p></div>';
}

?>

<div class="wrap chatcommerce-ai-sync">
	<h1><?php esc_html_e( 'Content Sync Dashboard', 'chatcommerce-ai' ); ?></h1>

	<div class="chatcommerce-ai-stats-grid">
		<div class="stat-card">
			<h3><?php esc_html_e( 'Total Indexed', 'chatcommerce-ai' ); ?></h3>
			<div class="stat-value"><?php echo number_format_i18n( $total_indexed ); ?></div>
		</div>

		<div class="stat-card">
			<h3><?php esc_html_e( 'Products', 'chatcommerce-ai' ); ?></h3>
			<div class="stat-value"><?php echo number_format_i18n( $products ); ?></div>
		</div>

		<div class="stat-card">
			<h3><?php esc_html_e( 'Pages', 'chatcommerce-ai' ); ?></h3>
			<div class="stat-value"><?php echo number_format_i18n( $pages ); ?></div>
		</div>

		<div class="stat-card">
			<h3><?php esc_html_e( 'Posts', 'chatcommerce-ai' ); ?></h3>
			<div class="stat-value"><?php echo number_format_i18n( $posts ); ?></div>
		</div>
	</div>

	<div class="chatcommerce-ai-sync-info" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0;">
		<h2><?php esc_html_e( 'Sync Status', 'chatcommerce-ai' ); ?></h2>

		<p>
			<strong><?php esc_html_e( 'Last Sync:', 'chatcommerce-ai' ); ?></strong>
			<?php
			if ( $last_synced ) {
				echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_synced ) );
			} else {
				esc_html_e( 'Never', 'chatcommerce-ai' );
			}
			?>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'chatcommerce_ai_sync' ); ?>
			<p>
				<button type="submit" name="trigger_sync" class="button button-primary">
					<?php esc_html_e( 'Run Full Sync Now', 'chatcommerce-ai' ); ?>
				</button>
			</p>
			<p class="description">
				<?php esc_html_e( 'This will re-index all selected content types. The process runs in the background.', 'chatcommerce-ai' ); ?>
			</p>
		</form>
	</div>

	<div class="notice notice-info inline">
		<p>
			<strong><?php esc_html_e( 'Automatic Sync:', 'chatcommerce-ai' ); ?></strong>
			<?php
			printf(
				/* translators: %s: settings page URL */
				__( 'Content is automatically synced when published or updated. You can configure the sync schedule in <a href="%s">Settings</a>.', 'chatcommerce-ai' ),
				esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=knowledge' ) )
			);
			?>
		</p>
	</div>
</div>
