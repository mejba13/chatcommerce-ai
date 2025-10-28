<?php
/**
 * Leads Page Template
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$leads_table = $wpdb->prefix . 'chatcommerce_leads';

// Pagination.
$per_page     = 20;
$current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$offset       = ( $current_page - 1 ) * $per_page;

// Get total count.
$total_leads = $wpdb->get_var( "SELECT COUNT(*) FROM {$leads_table}" );
$total_pages = ceil( $total_leads / $per_page );

// Get leads.
$leads = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$leads_table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$per_page,
		$offset
	)
);

?>

<div class="wrap chatcommerce-ai-leads">
	<h1><?php esc_html_e( 'Captured Leads', 'chatcommerce-ai' ); ?></h1>

	<div class="tablenav top">
		<div class="alignleft actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-leads&export=csv' ) ); ?>" class="button">
				<?php esc_html_e( 'Export to CSV', 'chatcommerce-ai' ); ?>
			</a>
		</div>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Email', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Phone', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Consent', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Date', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'chatcommerce-ai' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $leads ) ) : ?>
				<tr>
					<td colspan="6" style="text-align: center; padding: 20px;">
						<?php esc_html_e( 'No leads captured yet.', 'chatcommerce-ai' ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $leads as $lead ) : ?>
					<tr>
						<td><?php echo esc_html( $lead->name ?: '—' ); ?></td>
						<td><?php echo esc_html( $lead->email ?: '—' ); ?></td>
						<td><?php echo esc_html( $lead->phone ?: '—' ); ?></td>
						<td>
							<?php if ( $lead->consent ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
							<?php else : ?>
								<span class="dashicons dashicons-minus" style="color: #dba617;"></span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $lead->created_at ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-conversations&view=' . $lead->session_id ) ); ?>">
								<?php esc_html_e( 'View Conversation', 'chatcommerce-ai' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<?php
				echo paginate_links(
					array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $total_pages,
						'current'   => $current_page,
					)
				);
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
