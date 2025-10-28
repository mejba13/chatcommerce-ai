<?php
/**
 * Rate Limiter
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\Security;

/**
 * Rate limiting for API requests.
 */
class RateLimiter {
	/**
	 * Check rate limit.
	 *
	 * @param string $identifier Identifier (session ID or IP).
	 * @param int    $max_requests Max requests per window.
	 * @param int    $window_seconds Time window in seconds.
	 * @return bool
	 */
	public function check( $identifier, $max_requests = 20, $window_seconds = 60 ) {
		$cache_key = 'chatcommerce_ai_rate_limit_' . md5( $identifier );

		$requests = get_transient( $cache_key );

		if ( false === $requests ) {
			$requests = 0;
		}

		$requests++;

		if ( $requests > $max_requests ) {
			return false; // Rate limit exceeded.
		}

		set_transient( $cache_key, $requests, $window_seconds );

		return true;
	}

	/**
	 * Reset rate limit for identifier.
	 *
	 * @param string $identifier Identifier.
	 */
	public function reset( $identifier ) {
		$cache_key = 'chatcommerce_ai_rate_limit_' . md5( $identifier );
		delete_transient( $cache_key );
	}
}
