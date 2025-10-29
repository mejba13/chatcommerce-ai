<?php
/**
 * Sync Dashboard Template
 * Redesigned with Modern Classic Design System - Phase 2
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

// Get total possible items to calculate progress.
$total_products = wp_count_posts( 'product' )->publish ?? 0;
$total_pages    = wp_count_posts( 'page' )->publish ?? 0;
$total_posts    = wp_count_posts( 'post' )->publish ?? 0;
$total_content  = $total_products + $total_pages + $total_posts;

// Calculate percentages.
$products_percent = $total_products > 0 ? round( ( $products / $total_products ) * 100 ) : 100;
$pages_percent    = $total_pages > 0 ? round( ( $pages / $total_pages ) * 100 ) : 100;
$posts_percent    = $total_posts > 0 ? round( ( $posts / $total_posts ) * 100 ) : 100;
$overall_percent  = $total_content > 0 ? round( ( $total_indexed / $total_content ) * 100 ) : 100;

// Handle manual sync trigger.
if ( isset( $_POST['trigger_sync'] ) ) {
	check_admin_referer( 'chatcommerce_ai_sync' );

	// Trigger sync action.
	do_action( 'chatcommerce_ai_trigger_sync' );

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Full sync initiated. This may take a few minutes.', 'chatcommerce-ai' ) . '</p></div>';
}

?>

<div class="wrap chatcommerce-ai-wrap chatcommerce-ai-sync">
	<!-- Page Header -->
	<div class="cc-page-header">
		<div>
			<h1 class="cc-page-title"><?php esc_html_e( 'Content Sync', 'chatcommerce-ai' ); ?></h1>
			<p class="cc-text-small" style="margin: var(--cc-space-2) 0 0 0;">
				<?php
				printf(
					/* translators: %s: total indexed items */
					esc_html__( '%s items indexed and ready for AI', 'chatcommerce-ai' ),
					number_format_i18n( $total_indexed )
				);
				?>
			</p>
		</div>
		<div class="cc-page-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai' ) ); ?>" class="cc-button cc-button-ghost">
				<?php esc_html_e( 'Back to Dashboard', 'chatcommerce-ai' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=knowledge' ) ); ?>" class="cc-button cc-button-secondary">
				<span class="dashicons dashicons-admin-settings" style="margin-top: 3px;"></span>
				<?php esc_html_e( 'Sync Settings', 'chatcommerce-ai' ); ?>
			</a>
		</div>
	</div>

	<!-- Overall Progress Card -->
	<div class="cc-card cc-mb-6">
		<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--cc-space-4);">
			<div>
				<h2 style="margin: 0; font-size: var(--cc-text-lg); font-weight: 600;">
					<?php esc_html_e( 'Overall Sync Progress', 'chatcommerce-ai' ); ?>
				</h2>
				<p style="margin: var(--cc-space-2) 0 0 0; color: var(--cc-text-secondary); font-size: var(--cc-text-sm);">
					<?php
					if ( $last_synced ) {
						printf(
							/* translators: %s: last sync date */
							esc_html__( 'Last synced: %s', 'chatcommerce-ai' ),
							esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_synced ) )
						);
					} else {
						esc_html_e( 'Never synced', 'chatcommerce-ai' );
					}
					?>
				</p>
			</div>
			<div class="cc-progress-circular" style="--progress: <?php echo esc_attr( $overall_percent ); ?>;">
				<div class="cc-progress-circular-value">
					<?php echo esc_html( $overall_percent ); ?>%
				</div>
			</div>
		</div>

		<div class="cc-progress-bar">
			<div class="cc-progress-bar-fill" style="width: <?php echo esc_attr( $overall_percent ); ?>%;"></div>
		</div>

		<div style="display: flex; justify-content: space-between; margin-top: var(--cc-space-3); font-size: var(--cc-text-sm); color: var(--cc-text-secondary);">
			<span>
				<?php
				printf(
					/* translators: 1: indexed count, 2: total count */
					esc_html__( '%1$d of %2$d items synced', 'chatcommerce-ai' ),
					$total_indexed,
					$total_content
				);
				?>
			</span>
			<span>
				<?php
				$remaining = max( 0, $total_content - $total_indexed );
				printf(
					/* translators: %s: remaining count */
					esc_html( _n( '%s item remaining', '%s items remaining', $remaining, 'chatcommerce-ai' ) ),
					number_format_i18n( $remaining )
				);
				?>
			</span>
		</div>
	</div>

	<!-- Stats Grid -->
	<div class="cc-stats-grid" style="margin-bottom: var(--cc-space-8);">
		<!-- Products -->
		<div class="cc-stat-card">
			<div class="cc-stat-icon" style="background: var(--cc-color-primary-100); color: var(--cc-color-primary-600);">
				<span class="dashicons dashicons-products"></span>
			</div>
			<div class="cc-stat-content">
				<div class="cc-stat-label"><?php esc_html_e( 'Products', 'chatcommerce-ai' ); ?></div>
				<div class="cc-stat-value"><?php echo number_format_i18n( $products ); ?></div>
				<div class="cc-stat-trend">
					<div class="cc-progress-bar" style="margin-top: var(--cc-space-2);">
						<div class="cc-progress-bar-fill cc-progress-primary" style="width: <?php echo esc_attr( $products_percent ); ?>%;"></div>
					</div>
					<span style="font-size: var(--cc-text-xs); color: var(--cc-text-secondary); margin-top: var(--cc-space-1); display: block;">
						<?php
						printf(
							/* translators: 1: synced count, 2: total count */
							esc_html__( '%1$d of %2$d (%3$d%%)', 'chatcommerce-ai' ),
							$products,
							$total_products,
							$products_percent
						);
						?>
					</span>
				</div>
			</div>
		</div>

		<!-- Pages -->
		<div class="cc-stat-card">
			<div class="cc-stat-icon" style="background: var(--cc-color-success-100); color: var(--cc-color-success-600);">
				<span class="dashicons dashicons-admin-page"></span>
			</div>
			<div class="cc-stat-content">
				<div class="cc-stat-label"><?php esc_html_e( 'Pages', 'chatcommerce-ai' ); ?></div>
				<div class="cc-stat-value"><?php echo number_format_i18n( $pages ); ?></div>
				<div class="cc-stat-trend">
					<div class="cc-progress-bar" style="margin-top: var(--cc-space-2);">
						<div class="cc-progress-bar-fill cc-progress-success" style="width: <?php echo esc_attr( $pages_percent ); ?>%;"></div>
					</div>
					<span style="font-size: var(--cc-text-xs); color: var(--cc-text-secondary); margin-top: var(--cc-space-1); display: block;">
						<?php
						printf(
							/* translators: 1: synced count, 2: total count */
							esc_html__( '%1$d of %2$d (%3$d%%)', 'chatcommerce-ai' ),
							$pages,
							$total_pages,
							$pages_percent
						);
						?>
					</span>
				</div>
			</div>
		</div>

		<!-- Posts -->
		<div class="cc-stat-card">
			<div class="cc-stat-icon" style="background: var(--cc-color-info-100); color: var(--cc-color-info-600);">
				<span class="dashicons dashicons-admin-post"></span>
			</div>
			<div class="cc-stat-content">
				<div class="cc-stat-label"><?php esc_html_e( 'Posts', 'chatcommerce-ai' ); ?></div>
				<div class="cc-stat-value"><?php echo number_format_i18n( $posts ); ?></div>
				<div class="cc-stat-trend">
					<div class="cc-progress-bar" style="margin-top: var(--cc-space-2);">
						<div class="cc-progress-bar-fill cc-progress-info" style="width: <?php echo esc_attr( $posts_percent ); ?>%;"></div>
					</div>
					<span style="font-size: var(--cc-text-xs); color: var(--cc-text-secondary); margin-top: var(--cc-space-1); display: block;">
						<?php
						printf(
							/* translators: 1: synced count, 2: total count */
							esc_html__( '%1$d of %2$d (%3$d%%)', 'chatcommerce-ai' ),
							$posts,
							$total_posts,
							$posts_percent
						);
						?>
					</span>
				</div>
			</div>
		</div>

		<!-- Total Indexed -->
		<div class="cc-stat-card">
			<div class="cc-stat-icon" style="background: var(--cc-color-neutral-100); color: var(--cc-color-neutral-600);">
				<span class="dashicons dashicons-database"></span>
			</div>
			<div class="cc-stat-content">
				<div class="cc-stat-label"><?php esc_html_e( 'Total Indexed', 'chatcommerce-ai' ); ?></div>
				<div class="cc-stat-value"><?php echo number_format_i18n( $total_indexed ); ?></div>
				<div class="cc-stat-trend cc-stat-neutral">
					<?php esc_html_e( 'All content types', 'chatcommerce-ai' ); ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Sync Actions Card -->
	<div class="cc-card cc-mb-6">
		<h2 style="margin: 0 0 var(--cc-space-4) 0; font-size: var(--cc-text-lg); font-weight: 600;">
			<?php esc_html_e( 'Sync Actions', 'chatcommerce-ai' ); ?>
		</h2>

		<div style="display: grid; gap: var(--cc-space-4);">
			<div style="display: flex; align-items: start; gap: var(--cc-space-4);">
				<div style="flex: 1;">
					<h3 style="margin: 0 0 var(--cc-space-2) 0; font-size: var(--cc-text-base); font-weight: 600;">
						<?php esc_html_e( 'Manual Sync', 'chatcommerce-ai' ); ?>
					</h3>
					<p style="margin: 0; color: var(--cc-text-secondary); font-size: var(--cc-text-sm);">
						<?php esc_html_e( 'Trigger a full sync of all content. This will re-index all selected content types. The process runs in the background and may take a few minutes depending on your content volume.', 'chatcommerce-ai' ); ?>
					</p>
				</div>
				<form method="post" action="">
					<?php wp_nonce_field( 'chatcommerce_ai_sync' ); ?>
					<button type="submit" name="trigger_sync" class="cc-button cc-button-primary">
						<span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
						<?php esc_html_e( 'Run Full Sync', 'chatcommerce-ai' ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>

	<!-- Info Notice -->
	<div class="cc-alert cc-alert-info">
		<div class="cc-alert-icon">
			<span class="dashicons dashicons-info-outline"></span>
		</div>
		<div class="cc-alert-content">
			<div class="cc-alert-title"><?php esc_html_e( 'Automatic Sync', 'chatcommerce-ai' ); ?></div>
			<div class="cc-alert-message">
				<?php
				printf(
					/* translators: %s: settings page URL */
					__( 'Content is automatically synced when published or updated. You can configure which content types to sync and the sync schedule in <a href="%s" style="color: inherit; text-decoration: underline;">Sync Settings</a>.', 'chatcommerce-ai' ),
					esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings&tab=knowledge' ) )
				);
				?>
			</div>
		</div>
	</div>
</div>
