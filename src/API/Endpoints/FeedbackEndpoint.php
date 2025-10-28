<?php
/**
 * Feedback API Endpoint
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\API\Endpoints;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Feedback collection endpoint.
 */
class FeedbackEndpoint {
	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			'chatcommerce/v1',
			'/feedback',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'submit_feedback' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'session_id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'message_id' => array(
						'required'          => false,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'rating'     => array(
						'required'          => true,
						'type'              => 'integer',
						'validate_callback' => function ( $value ) {
							return $value === 0 || $value === 1;
						},
					),
					'comment'    => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);
	}

	/**
	 * Submit feedback.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function submit_feedback( $request ) {
		global $wpdb;

		$session_id = $request->get_param( 'session_id' );
		$message_id = $request->get_param( 'message_id' );
		$rating     = $request->get_param( 'rating' );
		$comment    = $request->get_param( 'comment' );

		// Store feedback.
		$feedback_table = $wpdb->prefix . 'chatcommerce_feedback';
		$inserted       = $wpdb->insert(
			$feedback_table,
			array(
				'message_id' => $message_id,
				'session_id' => $session_id,
				'rating'     => $rating,
				'comment'    => $comment,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%d', '%s', '%s' )
		);

		// Also update the message with the rating.
		if ( $message_id ) {
			$messages_table = $wpdb->prefix . 'chatcommerce_messages';
			$wpdb->update(
				$messages_table,
				array(
					'rating'        => $rating,
					'feedback_text' => $comment,
				),
				array( 'id' => $message_id ),
				array( '%d', '%s' ),
				array( '%d' )
			);
		}

		if ( ! $inserted ) {
			return new WP_Error(
				'feedback_failed',
				__( 'Failed to submit feedback.', 'chatcommerce-ai' ),
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Thank you for your feedback!', 'chatcommerce-ai' ),
			),
			200
		);
	}
}
