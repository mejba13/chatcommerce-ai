<?php
/**
 * Plugin Deactivator
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\Core;

/**
 * Handles plugin deactivation tasks.
 */
class Deactivator {
	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate() {
		self::unschedule_cron_jobs();
		self::flush_rewrite_rules();
	}

	/**
	 * Unschedule cron jobs.
	 */
	private static function unschedule_cron_jobs() {
		$timestamp = wp_next_scheduled( 'chatcommerce_ai_sync_content' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'chatcommerce_ai_sync_content' );
		}

		$timestamp = wp_next_scheduled( 'chatcommerce_ai_cleanup' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'chatcommerce_ai_cleanup' );
		}
	}

	/**
	 * Flush rewrite rules.
	 */
	private static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}
}
