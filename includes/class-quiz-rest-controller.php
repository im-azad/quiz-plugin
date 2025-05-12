<?php
/**
 * REST API Controller for Quiz Plugin
 *
 * @package QuizPlugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Quiz_REST_Controller
 * Handles REST API endpoints for the quiz plugin
 */
class Quiz_REST_Controller extends WP_REST_Controller {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->namespace = 'quiz-plugin/v1';
        $this->rest_base = 'quiz';
    }

    /**
     * Register routes
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_quiz'),
                    'permission_callback' => array($this, 'get_quiz_permissions_check'),
                    'args'                => array(
                        'id' => array(
                            'validate_callback' => function($param) {
                                return is_numeric($param);
                            }
                        ),
                    ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/submit',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'submit_quiz'),
                    'permission_callback' => '__return_true',
                    'args'                => array(
                        'quiz_id' => array(
                            'required' => true,
                            'type'     => 'integer',
                        ),
                        'answers' => array(
                            'required' => true,
                            'type'     => 'array',
                            'items'    => array(
                                'type' => 'integer'
                            ),
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * Check permissions for getting quiz data
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error
     */
    public function get_quiz_permissions_check($request) {
        $post = get_post($request['id']);

        if ($post === null) {
            return new WP_Error(
                'rest_post_invalid_id',
                __('Invalid post ID.', 'quiz-plugin'),
                array('status' => 404)
            );
        }

        if ('publish' !== $post->post_status && !current_user_can('read_post', $post->ID)) {
            return new WP_Error(
                'rest_cannot_read',
                __('Sorry, you are not allowed to read this quiz.', 'quiz-plugin'),
                array('status' => rest_authorization_required_code())
            );
        }

        return true;
    }

    /**
     * Get quiz data
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_quiz($request) {
        $post = get_post($request['id']);
        
        if (empty($post) || $post->post_type !== 'quiz') {
            return new WP_Error(
                'rest_post_invalid_id',
                __('Invalid quiz ID.', 'quiz-plugin'),
                array('status' => 404)
            );
        }

        $questions = get_post_meta($post->ID, 'quiz_questions', true);
        
        // Remove correct answers from the response
        $public_questions = array();
        foreach ($questions as $question) {
            $public_questions[] = array(
                'question' => $question['question'],
                'options'  => $question['options']
            );
        }

        $response = array(
            'id'        => $post->ID,
            'title'     => get_the_title($post->ID),
            'questions' => $public_questions
        );

        return rest_ensure_response($response);
    }

    /**
     * Handle quiz submission
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function submit_quiz($request) {
        $quiz_id = $request['quiz_id'];
        $submitted_answers = $request['answers'];

        $questions = get_post_meta($quiz_id, 'quiz_questions', true);
        
        if (empty($questions)) {
            return new WP_Error(
                'quiz_not_found',
                __('Quiz not found.', 'quiz-plugin'),
                array('status' => 404)
            );
        }

        if (count($submitted_answers) !== count($questions)) {
            return new WP_Error(
                'invalid_submission',
                __('Number of answers does not match number of questions.', 'quiz-plugin'),
                array('status' => 400)
            );
        }

        $correct_count = 0;
        $results = array();

        foreach ($questions as $index => $question) {
            $is_correct = isset($submitted_answers[$index]) && 
                         $submitted_answers[$index] === $question['correct_answer'];
            
            if ($is_correct) {
                $correct_count++;
            }

            $results[] = array(
                'question_index' => $index,
                'correct'       => $is_correct,
                'correct_answer' => $question['correct_answer']
            );
        }

        $score = array(
            'total_questions' => count($questions),
            'correct_answers' => $correct_count,
            'percentage'      => (count($questions) > 0) ? ($correct_count / count($questions) * 100) : 0,
            'results'         => $results
        );

        return rest_ensure_response($score);
    }
}