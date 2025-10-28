<?php
/**
 * Content Sync Manager
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\Sync;

/**
 * Manages content synchronization.
 */
class SyncManager {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Hook into post/product save events.
		add_action( 'save_post', array( $this, 'sync_post' ), 10, 2 );
		add_action( 'woocommerce_update_product', array( $this, 'sync_product' ) );
		add_action( 'delete_post', array( $this, 'delete_indexed_content' ) );

		// Scheduled sync.
		add_action( 'chatcommerce_ai_sync_content', array( $this, 'full_sync' ) );
		add_action( 'chatcommerce_ai_trigger_sync', array( $this, 'full_sync' ) );
	}

	/**
	 * Sync a post.
	 *
	 * @param int     $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 */
	public function sync_post( $post_id, $post ) {
		// Skip autosaves and revisions.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Check if post type should be synced.
		$settings = get_option( 'chatcommerce_ai_settings', array() );
		$sync_types = $settings['sync_post_types'] ?? array( 'post', 'page', 'product' );

		if ( ! in_array( $post->post_type, $sync_types, true ) ) {
			return;
		}

		// Only sync published posts.
		if ( $post->post_status !== 'publish' ) {
			return;
		}

		$this->index_content( $post_id, $post->post_type );
	}

	/**
	 * Sync a product.
	 *
	 * @param int $product_id Product ID.
	 */
	public function sync_product( $product_id ) {
		$this->index_content( $product_id, 'product' );
	}

	/**
	 * Index content.
	 *
	 * @param int    $doc_id Document ID.
	 * @param string $doc_type Document type.
	 */
	private function index_content( $doc_id, $doc_type ) {
		global $wpdb;

		$sync_table = $wpdb->prefix . 'chatcommerce_sync_index';

		if ( $doc_type === 'product' && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $doc_id );

			if ( ! $product ) {
				return;
			}

			$data = array(
				'doc_id'       => $doc_id,
				'doc_type'     => $doc_type,
				'title'        => $product->get_name(),
				'excerpt'      => $product->get_short_description(),
				'content'      => wp_strip_all_tags( $product->get_description() ),
				'url'          => $product->get_permalink(),
				'price'        => $product->get_price(),
				'stock_status' => $product->get_stock_status(),
				'sku'          => $product->get_sku(),
				'categories'   => wp_json_encode( wp_get_post_terms( $doc_id, 'product_cat', array( 'fields' => 'names' ) ) ),
				'tags'         => wp_json_encode( wp_get_post_terms( $doc_id, 'product_tag', array( 'fields' => 'names' ) ) ),
				'content_hash' => md5( $product->get_name() . $product->get_description() ),
				'last_synced'  => current_time( 'mysql' ),
			);
		} else {
			$post = get_post( $doc_id );

			if ( ! $post ) {
				return;
			}

			$data = array(
				'doc_id'       => $doc_id,
				'doc_type'     => $doc_type,
				'title'        => $post->post_title,
				'excerpt'      => $post->post_excerpt,
				'content'      => wp_strip_all_tags( $post->post_content ),
				'url'          => get_permalink( $doc_id ),
				'price'        => null,
				'stock_status' => null,
				'sku'          => null,
				'categories'   => wp_json_encode( wp_get_post_terms( $doc_id, 'category', array( 'fields' => 'names' ) ) ),
				'tags'         => wp_json_encode( wp_get_post_terms( $doc_id, 'post_tag', array( 'fields' => 'names' ) ) ),
				'content_hash' => md5( $post->post_title . $post->post_content ),
				'last_synced'  => current_time( 'mysql' ),
			);
		}

		// Check if already indexed.
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, content_hash FROM {$sync_table} WHERE doc_id = %d AND doc_type = %s",
				$doc_id,
				$doc_type
			)
		);

		if ( $existing ) {
			// Update if content changed.
			if ( $existing->content_hash !== $data['content_hash'] ) {
				$wpdb->update(
					$sync_table,
					$data,
					array(
						'doc_id'   => $doc_id,
						'doc_type' => $doc_type,
					)
				);
			}
		} else {
			// Insert new.
			$wpdb->insert( $sync_table, $data );
		}
	}

	/**
	 * Delete indexed content.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_indexed_content( $post_id ) {
		global $wpdb;

		$sync_table = $wpdb->prefix . 'chatcommerce_sync_index';
		$wpdb->delete(
			$sync_table,
			array( 'doc_id' => $post_id ),
			array( '%d' )
		);
	}

	/**
	 * Run full sync.
	 */
	public function full_sync() {
		$settings   = get_option( 'chatcommerce_ai_settings', array() );
		$sync_types = $settings['sync_post_types'] ?? array( 'post', 'page', 'product' );

		foreach ( $sync_types as $type ) {
			$posts = get_posts(
				array(
					'post_type'      => $type,
					'post_status'    => 'publish',
					'posts_per_page' => 100,
					'fields'         => 'ids',
				)
			);

			foreach ( $posts as $post_id ) {
				$this->index_content( $post_id, $type );
			}
		}
	}
}
