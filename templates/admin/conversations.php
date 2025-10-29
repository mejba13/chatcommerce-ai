<?php
/**
 * Conversations Page Template
 * Redesigned with Modern Classic Design System - Phase 2
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$sessions_table = $wpdb->prefix . 'chatcommerce_sessions';
$messages_table = $wpdb->prefix . 'chatcommerce_messages';

// Search and filters
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$filter_lead = isset( $_GET['lead'] ) ? sanitize_text_field( $_GET['lead'] ) : '';
$filter_date = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : '';

// Pagination
$per_page     = 20;
$current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$offset       = ( $current_page - 1 ) * $per_page;

// Build query
$where_clauses = array( '1=1' );

if ( ! empty( $search ) ) {
	$where_clauses[] = $wpdb->prepare( 'session_id LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
}

if ( $filter_lead === 'yes' ) {
	$where_clauses[] = 'lead_captured = 1';
} elseif ( $filter_lead === 'no' ) {
	$where_clauses[] = 'lead_captured = 0';
}

if ( $filter_date === 'today' ) {
	$where_clauses[] = 'DATE(started_at) = CURDATE()';
} elseif ( $filter_date === 'week' ) {
	$where_clauses[] = 'started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
} elseif ( $filter_date === 'month' ) {
	$where_clauses[] = 'started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
}

$where_sql = implode( ' AND ', $where_clauses );

// Get total count
$total_sessions = $wpdb->get_var( "SELECT COUNT(*) FROM {$sessions_table} WHERE {$where_sql}" );
$total_pages    = ceil( $total_sessions / $per_page );

// Get sessions
$sessions = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$sessions_table} WHERE {$where_sql} ORDER BY started_at DESC LIMIT %d OFFSET %d",
		$per_page,
		$offset
	)
);

?>

<div class="wrap chatcommerce-ai-wrap chatcommerce-ai-conversations">
	<!-- Page Header -->
	<div class="cc-page-header">
		<div>
			<h1 class="cc-page-title"><?php esc_html_e( 'Conversations', 'chatcommerce-ai' ); ?></h1>
			<p class="cc-text-small" style="margin: var(--cc-space-2) 0 0 0;">
				<?php
				printf(
					/* translators: %s: total sessions count */
					esc_html__( '%s total conversations', 'chatcommerce-ai' ),
					number_format_i18n( $total_sessions )
				);
				?>
			</p>
		</div>
		<div class="cc-page-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai' ) ); ?>" class="cc-button cc-button-ghost">
				<?php esc_html_e( 'Back to Dashboard', 'chatcommerce-ai' ); ?>
			</a>
			<a href="<?php echo esc_url( add_query_arg( 'export', 'csv' ) ); ?>" class="cc-button cc-button-secondary">
				<span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
				<?php esc_html_e( 'Export CSV', 'chatcommerce-ai' ); ?>
			</a>
		</div>
	</div>

	<!-- Search and Filters -->
	<div class="cc-card cc-mb-6">
		<form method="get" action="" class="cc-flex cc-gap-4 cc-items-center" style="flex-wrap: wrap;">
			<input type="hidden" name="page" value="chatcommerce-ai-conversations">

			<!-- Search -->
			<div class="cc-search-bar" style="flex: 1; min-width: 250px;">
				<span class="cc-search-icon dashicons dashicons-search"></span>
				<input
					type="search"
					name="s"
					value="<?php echo esc_attr( $search ); ?>"
					placeholder="<?php esc_attr_e( 'Search session ID...', 'chatcommerce-ai' ); ?>"
					class="cc-search-input"
				>
			</div>

			<!-- Lead Filter -->
			<select name="lead" class="cc-filter-select">
				<option value=""><?php esc_html_e( 'All Leads', 'chatcommerce-ai' ); ?></option>
				<option value="yes" <?php selected( $filter_lead, 'yes' ); ?>><?php esc_html_e( 'Lead Captured', 'chatcommerce-ai' ); ?></option>
				<option value="no" <?php selected( $filter_lead, 'no' ); ?>><?php esc_html_e( 'No Lead', 'chatcommerce-ai' ); ?></option>
			</select>

			<!-- Date Filter -->
			<select name="date" class="cc-filter-select">
				<option value=""><?php esc_html_e( 'All Time', 'chatcommerce-ai' ); ?></option>
				<option value="today" <?php selected( $filter_date, 'today' ); ?>><?php esc_html_e( 'Today', 'chatcommerce-ai' ); ?></option>
				<option value="week" <?php selected( $filter_date, 'week' ); ?>><?php esc_html_e( 'Last 7 Days', 'chatcommerce-ai' ); ?></option>
				<option value="month" <?php selected( $filter_date, 'month' ); ?>><?php esc_html_e( 'Last 30 Days', 'chatcommerce-ai' ); ?></option>
			</select>

			<!-- Submit -->
			<button type="submit" class="cc-button cc-button-primary">
				<?php esc_html_e( 'Filter', 'chatcommerce-ai' ); ?>
			</button>

			<?php if ( ! empty( $search ) || ! empty( $filter_lead ) || ! empty( $filter_date ) ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-conversations' ) ); ?>" class="cc-button cc-button-ghost">
					<?php esc_html_e( 'Clear', 'chatcommerce-ai' ); ?>
				</a>
			<?php endif; ?>
		</form>
	</div>

	<!-- Conversations Table -->
	<?php if ( empty( $sessions ) ) : ?>
		<div class="cc-empty-state">
			<svg class="cc-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
			</svg>
			<h3 class="cc-empty-state-title"><?php esc_html_e( 'No conversations found', 'chatcommerce-ai' ); ?></h3>
			<p class="cc-empty-state-description">
				<?php esc_html_e( 'Chat conversations will appear here once visitors start using the chatbot.', 'chatcommerce-ai' ); ?>
			</p>
			<div class="cc-empty-state-action">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings' ) ); ?>" class="cc-button cc-button-primary">
					<?php esc_html_e( 'Configure Chatbot', 'chatcommerce-ai' ); ?>
				</a>
			</div>
		</div>
	<?php else : ?>
		<div class="cc-table-wrapper">
			<table class="cc-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Session ID', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Started', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Last Activity', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Messages', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Lead', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'chatcommerce-ai' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $sessions as $session ) : ?>
						<tr>
							<td>
								<code style="font-size: var(--cc-text-xs); background: var(--cc-color-neutral-100); padding: var(--cc-space-1) var(--cc-space-2); border-radius: var(--cc-radius-sm);">
									<?php echo esc_html( substr( $session->session_id, 0, 12 ) . '...' ); ?>
								</code>
							</td>
							<td>
								<span style="white-space: nowrap;">
									<?php echo esc_html( mysql2date( get_option( 'date_format' ), $session->started_at ) ); ?>
								</span>
								<br>
								<small style="color: var(--cc-text-tertiary);">
									<?php echo esc_html( mysql2date( get_option( 'time_format' ), $session->started_at ) ); ?>
								</small>
							</td>
							<td>
								<span style="white-space: nowrap;">
									<?php echo esc_html( mysql2date( get_option( 'date_format' ), $session->last_activity ) ); ?>
								</span>
								<br>
								<small style="color: var(--cc-text-tertiary);">
									<?php echo esc_html( mysql2date( get_option( 'time_format' ), $session->last_activity ) ); ?>
								</small>
							</td>
							<td>
								<span class="cc-badge cc-badge-neutral">
									<?php echo esc_html( $session->message_count ); ?>
								</span>
							</td>
							<td>
								<?php if ( $session->lead_captured ) : ?>
									<span class="cc-badge cc-badge-success">
										<?php esc_html_e( 'Captured', 'chatcommerce-ai' ); ?>
									</span>
								<?php else : ?>
									<span class="cc-badge cc-badge-neutral">
										<?php esc_html_e( 'None', 'chatcommerce-ai' ); ?>
									</span>
								<?php endif; ?>
							</td>
							<td>
								<div class="cc-table-actions">
									<a
										href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-conversations&view=' . $session->session_id ) ); ?>"
										class="cc-table-action-btn"
										title="<?php esc_attr_e( 'View conversation', 'chatcommerce-ai' ); ?>"
									>
										<span class="dashicons dashicons-visibility"></span>
									</a>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php if ( $total_pages > 1 ) : ?>
				<div class="cc-table-pagination">
					<div class="cc-table-pagination-info">
						<?php
						$start = ( $current_page - 1 ) * $per_page + 1;
						$end   = min( $current_page * $per_page, $total_sessions );
						printf(
							/* translators: 1: start, 2: end, 3: total */
							esc_html__( 'Showing %1$d-%2$d of %3$d', 'chatcommerce-ai' ),
							$start,
							$end,
							$total_sessions
						);
						?>
					</div>
					<div class="cc-table-pagination-controls">
						<?php
						$pagination = paginate_links(
							array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span>',
								'next_text' => '<span class="dashicons dashicons-arrow-right-alt2"></span>',
								'total'     => $total_pages,
								'current'   => $current_page,
								'type'      => 'array',
							)
						);

						if ( $pagination ) {
							foreach ( $pagination as $link ) {
								echo '<span class="cc-button cc-button-sm">' . $link . '</span>';
							}
						}
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
