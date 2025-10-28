<?php
/**
 * REST API Router
 *
 * @package ChatCommerceAI
 */

namespace ChatCommerceAI\API;

use ChatCommerceAI\API\Endpoints\SessionEndpoint;
use ChatCommerceAI\API\Endpoints\ChatEndpoint;
use ChatCommerceAI\API\Endpoints\FeedbackEndpoint;
use ChatCommerceAI\API\Endpoints\LeadEndpoint;
use ChatCommerceAI\API\Endpoints\SuggestionsEndpoint;
use ChatCommerceAI\API\Endpoints\StatusEndpoint;

/**
 * Registers all REST API routes.
 */
class Router {
	/**
	 * API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'chatcommerce/v1';

	/**
	 * Endpoints.
	 *
	 * @var array
	 */
	private $endpoints = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->endpoints = array(
			new SessionEndpoint(),
			new ChatEndpoint(),
			new FeedbackEndpoint(),
			new LeadEndpoint(),
			new SuggestionsEndpoint(),
			new StatusEndpoint(),
		);
	}

	/**
	 * Register all routes.
	 */
	public function register_routes() {
		foreach ( $this->endpoints as $endpoint ) {
			$endpoint->register_routes();
		}
	}
}
