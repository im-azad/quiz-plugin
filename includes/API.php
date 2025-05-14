<?php
/**
 * API class
 *
 * @package Gutenblocks
 *
 * @since 1.0.1
 */

namespace QuizPlugin;

use QuizPlugin\API\Quiz;

/**
 * API class
 *
 * @since 1.0.1
 */
class API {
	/**
	 * Constructor
	 *
	 * @since 1.0.1
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_api' ) );
	}

	/**
	 * Register the API routes
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public function register_api() {
		$quiz = new Quiz();
		$quiz->register_routes();
	}
}
