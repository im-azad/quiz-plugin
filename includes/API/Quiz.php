<?php
/**
 * Quiz API
 *
 * @package Gutenblocks
 *
 * @since 1.0.1
 */

namespace QuizPlugin\API;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;

/**
 * Quiz API class
 *
 * @since 1.0.1
 */
class Quiz extends WP_REST_Controller {
	/**
	 * Constructor
	 *
	 * @since 1.0.1
	 */
	public function __construct() {
		$this->namespace = 'gutenblocks/v1';
		$this->rest_base = 'quizzes';
	}

	/**
	 * Register the routes for the quiz
	 *
	 * @since 1.0.1
	 */
	public function register_routes() {
		// Register the quiz create and get routes.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_quizzes' ),
					'permission_callback' => array( $this, 'get_quizzes_permission_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_quiz' ),
					'permission_callback' => array( $this, 'create_quiz_permission_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
			)
		);

		// Register the quiz update and delete routes.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_quiz' ),
					'permission_callback' => array( $this, 'update_quiz_permission_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_quiz' ),
					'permission_callback' => array( $this, 'delete_quiz_permission_check' ),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_quiz' ),
					'permission_callback' => '__return_true',
				),
			),
		);
	}

	/**
	 * Get all quizzes
	 *
	 * @since 1.0.1
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return array|\WP_Error
	 */
	public function get_quizzes( $request ) {
		$page     = $request->get_param( 'page' ) ? (int) $request->get_param( 'page' ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? (int) $request->get_param( 'per_page' ) : 10;

		$args = array(
			'post_type'      => 'quiz',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => 'publish',
		);

		// Get the quizzes.
		$query     = new \WP_Query( $args );
		$total     = $query->found_posts;
		$max_pages = ceil( $total / $per_page );

		// Format quizzes with meta data.
		$formatted_quizzes = array_map(
			function ( $quiz ) {
				return $this->prepare_item_for_response( $quiz, null );
			},
			$query->get_posts()
		);

		// Add headers.
		$response = new \WP_REST_Response( $formatted_quizzes );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', $max_pages );

		return $response;
	}

	/**
	 * Check if has permission to create a quiz
	 *
	 * @since 1.0.1
	 *
	 * @return bool
	 */
	public function get_quizzes_permission_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Create quiz permission check
	 *
	 * @since 1.0.1
	 */
	public function create_quiz_permission_check() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if has permission to update a quiz
	 *
	 * @since 1.0.1
	 *
	 * @return bool
	 */
	public function update_quiz_permission_check() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if has permission to delete a quiz
	 *
	 * @since 1.0.1
	 *
	 * @return bool
	 */
	public function delete_quiz_permission_check() {
		return current_user_can( 'delete_posts' );
	}

	/**
	 * Get the quiz permission check
	 *
	 * @since 1.0.1
	 *
	 * @return bool
	 */
	public function get_quiz_permission_check() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Prepare the quiz for the response
	 *
	 * @since 1.0.1
	 *
	 * @param \WP_Post         $item    The quiz object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Get questions and answers from meta.
		$questions       = json_decode( get_post_meta( $item->ID, 'quiz_questions', true ), true ) ?? array();
		$correct_answers = json_decode( get_post_meta( $item->ID, 'quiz_correct_answers', true ), true ) ?? array();

		$data = array(
			'id'              => $item->ID,
			'title'           => $item->post_title,
			'content'         => $item->post_content,
			'questions'       => $questions,
			'correct_answers' => $correct_answers,
		);

		return $data;
	}

	/**
	 * Get the collection parameters
	 *
	 * @since 1.0.1
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'     => array(
				'description'       => 'Current page of the collection.',
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description'       => 'Maximum number of items to be returned in result set.',
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
		);
	}

	/**
	 * Create a quiz
	 *
	 * @since 1.0.1
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return array|\WP_Error
	 */
	public function create_quiz( $request ) {
		// Get the title and content.
		$title   = $request->get_param( 'title' ) ?? '';
		$content = $request->get_param( 'content' ) ?? '';

		// Get questions and answers from request.
		$questions       = $request->get_param( 'questions' ) ?? array();
		$correct_answers = $request->get_param( 'correct_answers' ) ?? array();

		// Check if the title is empty.
		if ( empty( $title ) ) {
			return new WP_Error(
				'rest_quiz_title_empty',
				__( 'Please provide a title for the quiz.', 'gutenblocks' ),
				array( 'status' => 400 )
			);
		}

		// Validate questions and answers.
		if ( empty( $questions ) ) {
			return new WP_Error(
				'rest_quiz_questions_empty',
				__( 'Please provide at least one question.', 'gutenblocks' ),
				array( 'status' => 400 )
			);
		}

		if ( count( $questions ) !== count( $correct_answers ) ) {
			return new WP_Error(
				'rest_quiz_answers_mismatch',
				__( 'Number of correct answers must match number of questions.', 'gutenblocks' ),
				array( 'status' => 400 )
			);
		}

		// Create the quiz.
		$quiz_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'quiz',
			)
		);

		// Check if the quiz was created.
		if ( is_wp_error( $quiz_id ) ) {
			return $quiz_id;
		}

		// Format and store questions.
		$formatted_questions = array();
		foreach ( $questions as $index => $question ) {
			$formatted_questions[] = array(
				'question' => sanitize_text_field( $question['question'] ),
				'answers'  => array_map( 'sanitize_text_field', $question['answers'] ),
			);
		}

		// Store questions and correct answers as meta.
		update_post_meta( $quiz_id, 'quiz_questions', wp_json_encode( $formatted_questions ) );
		update_post_meta( $quiz_id, 'quiz_correct_answers', wp_json_encode( array_map( 'sanitize_text_field', $correct_answers ) ) );

		// Get the quiz.
		$quiz = get_post( $quiz_id );

		// Return the formatted response.
		return $this->prepare_item_for_response( $quiz, $request );
	}

	/**
	 * Update a quiz
	 *
	 * @since 1.0.1
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return array|\WP_Error
	 */
	public function update_quiz( $request ) {
		// Get the quiz ID.
		$quiz_id = $request->get_param( 'id' );

		// Get the title and content.
		$title   = $request->get_param( 'title' ) ?? '';
		$content = $request->get_param( 'content' ) ?? '';

		// Get questions and answers from request.
		$questions       = $request->get_param( 'questions' ) ?? array();
		$correct_answers = $request->get_param( 'correct_answers' ) ?? array();

		// Validate questions and answers.
		if ( ! empty( $questions ) && count( $questions ) !== count( $correct_answers ) ) {
			return new WP_Error(
				'rest_quiz_answers_mismatch',
				__( 'Number of correct answers must match number of questions.', 'gutenblocks' ),
				array( 'status' => 400 )
			);
		}

		// Update the quiz post.
		wp_update_post(
			array(
				'ID'           => $quiz_id,
				'post_title'   => $title,
				'post_content' => $content,
			)
		);

		// Format and update questions if provided.
		if ( ! empty( $questions ) ) {
			$formatted_questions = array();
			foreach ( $questions as $index => $question ) {
				$formatted_questions[] = array(
					'question' => sanitize_text_field( $question['question'] ),
					'answers'  => array_map( 'sanitize_text_field', $question['answers'] ),
				);
			}

			// Update post meta.
			update_post_meta( $quiz_id, 'quiz_questions', wp_json_encode( $formatted_questions ) );
			update_post_meta( $quiz_id, 'quiz_correct_answers', wp_json_encode( array_map( 'sanitize_text_field', $correct_answers ) ) );
		}

		// Get the quiz.
		$quiz = get_post( $quiz_id );

		// Return the formatted response.
		return $this->prepare_item_for_response( $quiz, $request );
	}

	/**
	 * Delete a quiz
	 *
	 * @since 1.0.1
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return array|\WP_Error
	 */
	public function delete_quiz( $request ) {
		// Get the quiz ID.
		$quiz_id = $request->get_param( 'id' );

		// Delete the quiz.
		wp_delete_post( $quiz_id, true );

		return array( 'message' => 'Quiz deleted successfully' );
	}

	/**
	 * Get a quiz
	 *
	 * @since 1.0.1
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return array|\WP_Error
	 */
	public function get_quiz( $request ) {
		// Get the quiz ID.
		$quiz_id = $request->get_param( 'id' );

		// Get the quiz.
		$quiz = get_post( $quiz_id );

		// Return the formatted response.
		return $this->prepare_item_for_response( $quiz, $request );
	}
}
