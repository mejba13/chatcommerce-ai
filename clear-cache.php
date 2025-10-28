<?php
/**
 * Clear Cache Helper
 * Run this file once to clear all WordPress caches
 *
 * Usage: Visit http://yoursite.local/wp-content/plugins/chatcommerce-ai/clear-cache.php
 * Then delete this file for security.
 */

// Load WordPress
require_once __DIR__ . '/../../../../wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Unauthorized' );
}

echo '<h1>ChatCommerce AI - Cache Clearer</h1>';
echo '<p>Clearing all caches...</p>';

// Clear WordPress object cache
wp_cache_flush();
echo '<p>✓ Object cache cleared</p>';

// Clear transients
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'" );
echo '<p>✓ Transients cleared</p>';

// Clear rewrite rules
flush_rewrite_rules();
echo '<p>✓ Rewrite rules flushed</p>';

// Bump plugin version to force asset reload
update_option( 'chatcommerce_ai_version_check', time() );
echo '<p>✓ Version check updated</p>';

echo '<hr>';
echo '<h2>✅ All caches cleared!</h2>';
echo '<p><strong>Next steps:</strong></p>';
echo '<ol>';
echo '<li>Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)</li>';
echo '<li>Clear browser cache completely if needed</li>';
echo '<li><strong>Delete this file (clear-cache.php) for security</strong></li>';
echo '</ol>';

echo '<hr>';
echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
