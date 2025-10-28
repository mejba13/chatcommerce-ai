<?php
/**
 * Stock Check Tool
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\AI\Tools;

/**
 * Check product stock status.
 */
class StockCheckTool {
	/**
	 * Check stock.
	 *
	 * @param int $product_id Product ID.
	 * @return array
	 */
	public function check( $product_id ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return array(
				'error' => 'WooCommerce not available',
			);
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return array(
				'error' => 'Product not found',
			);
		}

		return array(
			'product_id'   => $product_id,
			'title'        => $product->get_name(),
			'in_stock'     => $product->is_in_stock(),
			'stock_status' => $product->get_stock_status(),
			'stock_qty'    => $product->get_stock_quantity(),
			'backorders'   => $product->get_backorders(),
		);
	}
}
