<?php
/**
 * Feedback Settings Tab
 *
 * @package ChatCommerceAI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h2><?php esc_html_e( 'Feedback Settings', 'chatcommerce-ai' ); ?></h2>

<table class="form-table">
	<tr>
		<th scope="row">
			<label for="feedback_enabled"><?php esc_html_e( 'Enable Feedback', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[feedback_enabled]"
					id="feedback_enabled"
					value="1"
					<?php checked( ! empty( $settings['feedback_enabled'] ) || ! isset( $settings['feedback_enabled'] ) ); ?>
				/>
				<?php esc_html_e( 'Allow users to rate AI responses', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Shows thumbs up/down buttons on each AI response.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="feedback_comment"><?php esc_html_e( 'Allow Feedback Comments', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[feedback_comment]"
					id="feedback_comment"
					value="1"
					<?php checked( ! empty( $settings['feedback_comment'] ) || ! isset( $settings['feedback_comment'] ) ); ?>
				/>
				<?php esc_html_e( 'Let users add optional text comments with their rating', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Collect additional context about why users rated responses positively or negatively.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="nps_enabled"><?php esc_html_e( 'NPS Survey', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<label>
				<input
					type="checkbox"
					name="chatcommerce_ai[nps_enabled]"
					id="nps_enabled"
					value="1"
					<?php checked( ! empty( $settings['nps_enabled'] ) ); ?>
				/>
				<?php esc_html_e( 'Show occasional NPS (Net Promoter Score) survey', 'chatcommerce-ai' ); ?>
			</label>
			<p class="setting-description">
				<?php esc_html_e( 'Asks users to rate their overall experience on a scale of 0-10.', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="nps_frequency"><?php esc_html_e( 'NPS Frequency', 'chatcommerce-ai' ); ?></label>
		</th>
		<td>
			<select name="chatcommerce_ai[nps_frequency]" id="nps_frequency">
				<option value="10" <?php selected( $settings['nps_frequency'] ?? '10', '10' ); ?>>
					<?php esc_html_e( 'Every 10 sessions', 'chatcommerce-ai' ); ?>
				</option>
				<option value="25" <?php selected( $settings['nps_frequency'] ?? '10', '25' ); ?>>
					<?php esc_html_e( 'Every 25 sessions', 'chatcommerce-ai' ); ?>
				</option>
				<option value="50" <?php selected( $settings['nps_frequency'] ?? '10', '50' ); ?>>
					<?php esc_html_e( 'Every 50 sessions', 'chatcommerce-ai' ); ?>
				</option>
			</select>
			<p class="setting-description">
				<?php esc_html_e( 'How often to show the NPS survey (per user/session).', 'chatcommerce-ai' ); ?>
			</p>
		</td>
	</tr>
</table>
