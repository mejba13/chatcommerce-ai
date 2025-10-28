<?php
/**
 * Instructions Settings Tab
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_version = get_option( 'chatcommerce_ai_prompt_active', 'v1' );
$current_prompt = get_option( "chatcommerce_ai_prompt_{$active_version}", '' );

// Handle prompt update.
if ( isset( $_POST['chatcommerce_ai_prompt_submit'] ) ) {
	check_admin_referer( 'chatcommerce_ai_prompt' );

	$new_prompt = wp_kses_post( $_POST['system_prompt'] ?? '' );
	update_option( "chatcommerce_ai_prompt_{$active_version}", $new_prompt );

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'System instructions updated successfully.', 'chatcommerce-ai' ) . '</p></div>';
	$current_prompt = $new_prompt;
}

?>

<h2><?php esc_html_e( 'System Instructions', 'chatcommerce-ai' ); ?></h2>

<p>
	<?php esc_html_e( 'Customize how the AI assistant behaves and responds to customers. Use variables like {site_name}, {store_url}, {currency} to personalize instructions.', 'chatcommerce-ai' ); ?>
</p>

<form method="post" action="">
	<?php wp_nonce_field( 'chatcommerce_ai_prompt' ); ?>

	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="system_prompt"><?php esc_html_e( 'System Prompt', 'chatcommerce-ai' ); ?></label>
			</th>
			<td>
				<?php
				wp_editor(
					$current_prompt,
					'system_prompt',
					array(
						'textarea_name' => 'system_prompt',
						'textarea_rows' => 20,
						'media_buttons' => false,
						'teeny'         => false,
						'quicktags'     => true,
						'tinymce'       => array(
							'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
						),
					)
				);
				?>
				<p class="setting-description">
					<?php esc_html_e( 'Define the AI\'s personality, tone, and behavior guidelines.', 'chatcommerce-ai' ); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label><?php esc_html_e( 'Available Variables', 'chatcommerce-ai' ); ?></label>
			</th>
			<td>
				<code>{site_name}</code> - <?php echo esc_html( get_bloginfo( 'name' ) ); ?><br/>
				<code>{store_url}</code> - <?php echo esc_url( get_home_url() ); ?><br/>
				<code>{currency}</code> - <?php echo esc_html( get_woocommerce_currency() ); ?><br/>
				<p class="setting-description">
					<?php esc_html_e( 'These variables will be automatically replaced with actual values when the AI processes requests.', 'chatcommerce-ai' ); ?>
				</p>
			</td>
		</tr>
	</table>

	<?php submit_button( __( 'Save Instructions', 'chatcommerce-ai' ), 'primary', 'chatcommerce_ai_prompt_submit' ); ?>
</form>

<div class="notice notice-info inline" style="margin-top: 20px;">
	<p>
		<strong><?php esc_html_e( 'Tips for writing effective instructions:', 'chatcommerce-ai' ); ?></strong>
	</p>
	<ul style="list-style: disc; margin-left: 20px;">
		<li><?php esc_html_e( 'Be specific about the tone and style you want', 'chatcommerce-ai' ); ?></li>
		<li><?php esc_html_e( 'Include your return/shipping policies', 'chatcommerce-ai' ); ?></li>
		<li><?php esc_html_e( 'Specify what the AI should NOT do (e.g., "never process payments directly")', 'chatcommerce-ai' ); ?></li>
		<li><?php esc_html_e( 'Tell it to use the available tools for product lookups', 'chatcommerce-ai' ); ?></li>
	</ul>
</div>
