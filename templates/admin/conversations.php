<?php
/**
 * Conversations Page Template
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

// Pagination.
$per_page     = 20;
$current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$offset       = ( $current_page - 1 ) * $per_page;

// Get total count.
$total_sessions = $wpdb->get_var( "SELECT COUNT(*) FROM {$sessions_table}" );
$total_pages    = ceil( $total_sessions / $per_page );

// Get sessions.
$sessions = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$sessions_table} ORDER BY started_at DESC LIMIT %d OFFSET %d",
		$per_page,
		$offset
	)
);

?>

<div class="wrap chatcommerce-ai-conversations">
	<h1><?php esc_html_e( 'Chat Conversations', 'chatcommerce-ai' ); ?></h1>

	<div class="tablenav top">
		<div class="alignleft actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-conversations&export=csv' ) ); ?>" class="button">
				<?php esc_html_e( 'Export to CSV', 'chatcommerce-ai' ); ?>
			</a>
		</div>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Session ID', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Started', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Last Activity', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Messages', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Lead Captured', 'chatcommerce-ai' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'chatcommerce-ai' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $sessions ) ) : ?>
				<tr>
					<td colspan="6" style="text-align: center; padding: 20px;">
						<?php esc_html_e( 'No conversations yet.', 'chatcommerce-ai' ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $sessions as $session ) : ?>
					<tr>
						<td><code><?php echo esc_html( substr( $session->session_id, 0, 12 ) . '...' ); ?></code></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $session->started_at ) ); ?></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $session->last_activity ) ); ?></td>
						<td><?php echo esc_html( $session->message_count ); ?></td>
						<td>
							<?php if ( $session->lead_captured ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
							<?php else : ?>
								<span class="dashicons dashicons-minus" style="color: #dba617;"></span>
							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-conversations&view=' . $session->session_id ) ); ?>">
								<?php esc_html_e( 'View', 'chatcommerce-ai' ); ?>
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
