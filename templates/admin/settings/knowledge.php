<?php
/**
 * Knowledge & Sync Settings Tab
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h2><?php esc_html_e( 'Knowledge & Sync Settings', 'chatcommerce-ai' ); ?></h2>

<table class="form-table">
	<tr>
		<th scope="row">
			<label><?php esc_html_e( 'Content Types to Sync', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<fieldset>
				<label>
					<input
						type="checkbox"
						name="chatcommerce_ai[sync_post_types][]"
						value="post"
						<?php checked( in_array( 'post', $settings['sync_post_types'] ?? array( 'post', 'page', 'product' ), true ) ); ?>
					/>
					<?php esc_html_e( 'Posts', 'chatcommerce-ai' ); ?>
				</label><br/>

				<label>
					<input
						type="checkbox"
						name="chatcommerce_ai[sync_post_types][]"
						value="page"
						<?php checked( in_array( 'page', $settings['sync_post_types'] ?? array( 'post', 'page', 'product' ), true ) ); ?>
					/>
					<?php esc_html_e( 'Pages', 'chatcommerce-ai' ); ?>
				</label><br/>

				<label>
					<input
						type="checkbox"
						name="chatcommerce_ai[sync_post_types][]"
						value="product"
						<?php checked( in_array( 'product', $settings['sync_post_types'] ?? array( 'post', 'page', 'product' ), true ) ); ?>
					/>
					<?php esc_html_e( 'Products (WooCommerce)', 'chatcommerce-ai' ); ?>
				</label>
			</fieldset>
			<p class="setting-description">
				<?php esc_html_e( 'Select which content types the AI should have knowledge of.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="sync_schedule"><?php esc_html_e( 'Sync Schedule', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[sync_schedule]" id="sync_schedule">
				<option value="hourly" <?php selected( $settings['sync_schedule'] ?? 'hourly', 'hourly' ); ?>>
					<?php esc_html_e( 'Every Hour', 'chatcommerce-ai' ); ?>
				</option>
				<option value="twicedaily" <?php selected( $settings['sync_schedule'] ?? 'hourly', 'twicedaily' ); ?>>
					<?php esc_html_e( 'Twice Daily', 'chatcommerce-ai' ); ?>
				</option>
				<option value="daily" <?php selected( $settings['sync_schedule'] ?? 'hourly', 'daily' ); ?>>
					<?php esc_html_e( 'Daily', 'chatcommerce-ai' ); ?>
				</option>
				<option value="manual" <?php selected( $settings['sync_schedule'] ?? 'hourly', 'manual' ); ?>>
					<?php esc_html_e( 'Manual Only', 'chatcommerce-ai' ); ?>
				</option>
			</select>
			<p class="setting-description">
				<?php esc_html_e( 'How often to sync content changes. Content is also synced immediately when published/updated.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label><?php esc_html_e( 'Product Fields to Index', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<fieldset>
				<label>
					<input type="checkbox" checked disabled />
					<?php esc_html_e( 'Title', 'chatcommerce-ai' ); ?>
				</label><br/>
				<label>
					<input type="checkbox" checked disabled />
					<?php esc_html_e( 'Description', 'chatcommerce-ai' ); ?>
				</label><br/>
				<label>
					<input type="checkbox" checked disabled />
					<?php esc_html_e( 'Price', 'chatcommerce-ai' ); ?>
				</label><br/>
				<label>
					<input type="checkbox" checked disabled />
					<?php esc_html_e( 'Stock Status', 'chatcommerce-ai' ); ?>
				</label><br/>
				<label>
					<input type="checkbox" checked disabled />
					<?php esc_html_e( 'SKU', 'chatcommerce-ai' ); ?>
				</label><br/>
				<label>
					<input type="checkbox" checked disabled />
					<?php esc_html_e( 'Categories & Tags', 'chatcommerce-ai' ); ?>
				</label>
			</fieldset>
			<p class="setting-description">
				<?php esc_html_e( 'All fields are indexed automatically.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label><?php esc_html_e( 'Manual Sync', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=chatcommerce-ai-sync' ) ); ?>" class="button">
				<?php esc_html_e( 'Go to Sync Dashboard', 'chatcommerce-ai' ); ?>
			</a>
			<p class="setting-description">
				<?php esc_html_e( 'Trigger a full content sync or view sync status.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>
</table>
