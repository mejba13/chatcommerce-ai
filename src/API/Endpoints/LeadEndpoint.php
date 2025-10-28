<?php
/**
 * Lead Capture API Endpoint
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\API\Endpoints;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Lead capture endpoint.
 */
class LeadEndpoint {
	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'chatcommerce/v1',
			'/lead',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'capture_lead' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'session_id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'name'       => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'email'      => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_email',
					),
					'phone'      => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'consent'    => array(
						'required'          => true,
						'type'              => 'boolean',
					),
				),
			)
		);
	}

	/**
	 * Capture lead.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function capture_lead( $request ) {
		global $wpdb;

		$session_id = $request->get_param( 'session_id' );
		$name       = $request->get_param( 'name' );
		$email      = $request->get_param( 'email' );
		$phone      = $request->get_param( 'phone' );
		$consent    = $request->get_param( 'consent' );

		// Require consent.
		if ( ! $consent ) {
			return new WP_Error(
				'consent_required',
				__( 'Consent is required to capture lead information.', 'chatcommerce-ai' ),
				array( 'status' => 400 )
			);
		}

		// Validate email.
		if ( ! is_email( $email ) ) {
			return new WP_Error(
				'invalid_email',
				__( 'Please provide a valid email address.', 'chatcommerce-ai' ),
				array( 'status' => 400 )
			);
		}

		// Store lead.
		$leads_table = $wpdb->prefix . 'chatcommerce_leads';
		$inserted    = $wpdb->insert(
			$leads_table,
			array(
				'session_id' => $session_id,
				'name'       => $name,
				'email'      => $email,
				'phone'      => $phone,
				'consent'    => $consent ? 1 : 0,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		if ( ! $inserted ) {
			return new WP_Error(
				'lead_capture_failed',
				__( 'Failed to capture lead.', 'chatcommerce-ai' ),
				array( 'status' => 500 )
			);
		}

		// Update session.
		$sessions_table = $wpdb->prefix . 'chatcommerce_sessions';
		$wpdb->update(
			$sessions_table,
			array( 'lead_captured' => 1 ),
			array( 'session_id' => $session_id ),
			array( '%d' ),
			array( '%s' )
		);

		// Send notification email.
		$this->send_lead_notification( $name, $email, $phone );

		// Trigger webhook.
		$this->trigger_webhook( $session_id, $name, $email, $phone );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Thank you! We\'ll be in touch soon.', 'chatcommerce-ai' ),
			),
			200
		);
	}

	/**
	 * Send lead notification email.
	 *
	 * @param string $name Name.
	 * @param string $email Email.
	 * @param string $phone Phone.
	 */
	private function send_lead_notification( $name, $email, $phone ) {
		$settings = get_option( 'chatcommerce_ai_settings', array() );
		$to       = $settings['lead_notification_email'] ?? get_option( 'admin_email' );

		$subject = sprintf(
			/* translators: %s: site name */
			__( 'New Lead from ChatCommerce AI - %s', 'chatcommerce-ai' ),
			get_bloginfo( 'name' )
		);

		$message = sprintf(
			__( "New lead captured via ChatCommerce AI:\n\nName: %s\nEmail: %s\nPhone: %s\n\nView in admin: %s", 'chatcommerce-ai' ),
			$name,
			$email,
			$phone,
			admin_url( 'admin.php?page=chatcommerce-ai-leads' )
		);

		wp_mail( $to, $subject, $message );
	}

	/**
	 * Trigger webhook.
	 *
	 * @param string $session_id Session ID.
	 * @param string $name Name.
	 * @param string $email Email.
	 * @param string $phone Phone.
	 */
	private function trigger_webhook( $session_id, $name, $email, $phone ) {
		$settings    = get_option( 'chatcommerce_ai_settings', array() );
		$webhook_url = $settings['lead_webhook_url'] ?? '';

		if ( empty( $webhook_url ) ) {
			return;
		}

		wp_remote_post(
			$webhook_url,
			array(
				'body'    => wp_json_encode(
					array(
						'session_id' => $session_id,
						'name'       => $name,
						'email'      => $email,
						'phone'      => $phone,
						'timestamp'  => current_time( 'c' ),
					)
				),
				'headers' => array(
					'Content-Type' => 'application/json',
				),
			)
		);
	}
}
