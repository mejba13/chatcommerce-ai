<?php
/**
 * Leads Page Template
 * Redesigned with Modern Classic Design System - Phase 2
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$leads_table = $wpdb->prefix . 'chatcommerce_leads';

// Search and filters
$search        = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$filter_consent = isset( $_GET['consent'] ) ? sanitize_text_field( $_GET['consent'] ) : '';
$filter_date   = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : '';

// Pagination
$per_page     = 20;
$current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$offset       = ( $current_page - 1 ) * $per_page;

// Build query
$where_clauses = array( '1=1' );

if ( ! empty( $search ) ) {
	$where_clauses[] = $wpdb->prepare(
		'(name LIKE %s OR email LIKE %s OR phone LIKE %s)',
		'%' . $wpdb->esc_like( $search ) . '%',
		'%' . $wpdb->esc_like( $search ) . '%',
		'%' . $wpdb->esc_like( $search ) . '%'
	);
}

if ( $filter_consent === 'yes' ) {
	$where_clauses[] = 'consent = 1';
} elseif ( $filter_consent === 'no' ) {
	$where_clauses[] = 'consent = 0';
}

if ( $filter_date === 'today' ) {
	$where_clauses[] = 'DATE(created_at) = CURDATE()';
} elseif ( $filter_date === 'week' ) {
	$where_clauses[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
} elseif ( $filter_date === 'month' ) {
	$where_clauses[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
}

$where_sql = implode( ' AND ', $where_clauses );

// Get total count
$total_leads = $wpdb->get_var( "SELECT COUNT(*) FROM {$leads_table} WHERE {$where_sql}" );
$total_pages = ceil( $total_leads / $per_page );

// Get leads
$leads = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$leads_table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$per_page,
		$offset
	)
);

?>

<div class="wrap chatcommerce-ai-wrap chatcommerce-ai-leads">
	<!-- Page Header -->
	<div class="cc-page-header">
		<div>
			<h1 class="cc-page-title"><?php esc_html_e( 'Captured Leads', 'chatcommerce-ai' ); ?></h1>
			<p class="cc-text-small" style="margin: var(--cc-space-2) 0 0 0;">
				<?php
				printf(
					/* translators: %s: total leads count */
					esc_html__( '%s total leads', 'chatcommerce-ai' ),
					number_format_i18n( $total_leads )
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
			<input type="hidden" name="page" value="chatcommerce-ai-leads">

			<!-- Search -->
			<div class="cc-search-bar" style="flex: 1; min-width: 250px;">
				<span class="cc-search-icon dashicons dashicons-search"></span>
				<input
					type="search"
					name="s"
					value="<?php echo esc_attr( $search ); ?>"
					placeholder="<?php esc_attr_e( 'Search by name, email, or phone...', 'chatcommerce-ai' ); ?>"
					class="cc-search-input"
				>
			</div>

			<!-- Consent Filter -->
			<select name="consent" class="cc-filter-select">
				<option value=""><?php esc_html_e( 'All Leads', 'chatcommerce-ai' ); ?></option>
				<option value="yes" <?php selected( $filter_consent, 'yes' ); ?>><?php esc_html_e( 'With Consent', 'chatcommerce-ai' ); ?></option>
				<option value="no" <?php selected( $filter_consent, 'no' ); ?>><?php esc_html_e( 'No Consent', 'chatcommerce-ai' ); ?></option>
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

			<?php if ( ! empty( $search ) || ! empty( $filter_consent ) || ! empty( $filter_date ) ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-leads' ) ); ?>" class="cc-button cc-button-ghost">
					<?php esc_html_e( 'Clear', 'chatcommerce-ai' ); ?>
				</a>
			<?php endif; ?>
		</form>
	</div>

	<!-- Leads Table -->
	<?php if ( empty( $leads ) ) : ?>
		<div class="cc-empty-state">
			<svg class="cc-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
			</svg>
			<h3 class="cc-empty-state-title"><?php esc_html_e( 'No leads found', 'chatcommerce-ai' ); ?></h3>
			<p class="cc-empty-state-description">
				<?php esc_html_e( 'Leads captured through the chatbot will appear here. Enable lead capture in settings to start collecting customer information.', 'chatcommerce-ai' ); ?>
			</p>
			<div class="cc-empty-state-action">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-settings' ) ); ?>" class="cc-button cc-button-primary">
					<?php esc_html_e( 'Configure Lead Capture', 'chatcommerce-ai' ); ?>
				</a>
			</div>
		</div>
	<?php else : ?>
		<div class="cc-table-wrapper">
			<table class="cc-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Email', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Consent', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Captured', 'chatcommerce-ai' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'chatcommerce-ai' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $leads as $lead ) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $lead->name ?: '—' ); ?></strong>
							</td>
							<td>
								<?php if ( $lead->email ) : ?>
									<a href="mailto:<?php echo esc_attr( $lead->email ); ?>" style="color: var(--cc-color-primary-600);">
										<?php echo esc_html( $lead->email ); ?>
									</a>
								<?php else : ?>
									<span style="color: var(--cc-text-tertiary);">—</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $lead->phone ) : ?>
									<a href="tel:<?php echo esc_attr( $lead->phone ); ?>" style="color: var(--cc-color-primary-600);">
										<?php echo esc_html( $lead->phone ); ?>
									</a>
								<?php else : ?>
									<span style="color: var(--cc-text-tertiary);">—</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $lead->consent ) : ?>
									<span class="cc-badge cc-badge-success">
										<?php esc_html_e( 'Yes', 'chatcommerce-ai' ); ?>
									</span>
								<?php else : ?>
									<span class="cc-badge cc-badge-warning">
										<?php esc_html_e( 'No', 'chatcommerce-ai' ); ?>
									</span>
								<?php endif; ?>
							</td>
							<td>
								<span style="white-space: nowrap;">
									<?php echo esc_html( mysql2date( get_option( 'date_format' ), $lead->created_at ) ); ?>
								</span>
								<br>
								<small style="color: var(--cc-text-tertiary);">
									<?php echo esc_html( mysql2date( get_option( 'time_format' ), $lead->created_at ) ); ?>
								</small>
							</td>
							<td>
								<div class="cc-table-actions">
									<a
										href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-conversations&view=' . $lead->session_id ) ); ?>"
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
						$end   = min( $current_page * $per_page, $total_leads );
						printf(
							/* translators: 1: start, 2: end, 3: total */
							esc_html__( 'Showing %1$d-%2$d of %3$d', 'chatcommerce-ai' ),
							$start,
							$end,
							$total_leads
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
