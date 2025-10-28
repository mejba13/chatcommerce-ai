<?php
/**
 * Product Search Tool
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\AI\Tools;

/**
 * Search for products.
 */
class ProductSearchTool {
	/**
	 * Search products.
	 *
	 * @param string $query Search query.
	 * @return array
	 */
	public function search( $query ) {
		global $wpdb;

		$sync_table = $wpdb->prefix . 'chatcommerce_sync_index';

		// Search in indexed content.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$sync_table}
				WHERE doc_type = 'product'
				AND (
					MATCH(title, excerpt, content) AGAINST(%s IN NATURAL LANGUAGE MODE)
					OR sku LIKE %s
					OR title LIKE %s
				)
				LIMIT 5",
				$query,
				'%' . $wpdb->esc_like( $query ) . '%',
				'%' . $wpdb->esc_like( $query ) . '%'
			)
		);

		if ( empty( $results ) ) {
			return array(
				'found'    => false,
				'message'  => 'No products found matching your query.',
				'products' => array(),
			);
		}

		$products = array();

		foreach ( $results as $result ) {
			$products[] = array(
				'id'           => $result->doc_id,
				'title'        => $result->title,
				'excerpt'      => $result->excerpt,
				'url'          => $result->url,
				'price'        => $result->price,
				'stock_status' => $result->stock_status,
				'sku'          => $result->sku,
			);
		}

		return array(
			'found'    => true,
			'count'    => count( $products ),
			'products' => $products,
		);
	}
}
