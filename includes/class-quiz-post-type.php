<?php
/**
 * Register Quiz Custom Post Type and Meta Fields
 *
 * @package QuizPlugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Quiz_Post_Type
 * Handles registration of the quiz custom post type and its meta fields
 */
class Quiz_Post_Type {
    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_meta_fields'));
        add_action('rest_api_init', array($this, 'register_rest_fields'));
    }

    /**
     * Register the custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Quizzes', 'quiz-plugin'),
            'singular_name'      => __('Quiz', 'quiz-plugin'),
            'menu_name'          => __('Quizzes', 'quiz-plugin'),
            'add_new'            => __('Add New', 'quiz-plugin'),
            'add_new_item'       => __('Add New Quiz', 'quiz-plugin'),
            'edit_item'          => __('Edit Quiz', 'quiz-plugin'),
            'new_item'           => __('New Quiz', 'quiz-plugin'),
            'view_item'          => __('View Quiz', 'quiz-plugin'),
            'search_items'       => __('Search Quizzes', 'quiz-plugin'),
            'not_found'          => __('No quizzes found', 'quiz-plugin'),
            'not_found_in_trash' => __('No quizzes found in Trash', 'quiz-plugin'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'rest_base'           => 'quizzes',
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-welcome-learn-more',
            'hierarchical'        => false,
            'supports'            => array('title', 'editor', 'custom-fields'),
            'has_archive'         => true,
            'rewrite'             => array('slug' => 'quizzes'),
            'show_in_rest'        => true,
        );

        register_post_type('quiz', $args);
    }

    /**
     * Register meta fields for the quiz post type
     */
    public function register_meta_fields() {
        register_post_meta('quiz', 'quiz_questions', array(
            'type'          => 'array',
            'description'    => 'Quiz questions and answers',
            'single'         => true,
            'show_in_rest'   => array(
                'schema' => array(
                    'type'  => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'question' => array(
                                'type' => 'string',
                                'required' => true
                            ),
                            'options' => array(
                                'type' => 'array',
                                'items' => array(
                                    'type' => 'string'
                                ),
                                'required' => true
                            ),
                            'correct_answer' => array(
                                'type' => 'integer',
                                'required' => true
                            )
                        )
                    )
                )
            ),
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));
    }

    /**
     * Register REST API fields
     */
    public function register_rest_fields() {
        register_rest_field('quiz', 'quiz_data', array(
            'get_callback' => array($this, 'get_quiz_data'),
            'schema' => array(
                'description' => 'Quiz data including questions and answers',
                'type'        => 'object',
                'context'     => array('view', 'edit')
            )
        ));
    }

    /**
     * Get quiz data for REST API
     *
     * @param array $object Details of current post.
     * @return array Quiz data
     */
    public function get_quiz_data($object) {
        $post_id = $object['id'];
        return array(
            'questions' => get_post_meta($post_id, 'quiz_questions', true)
        );
    }
}

// Initialize the class
new Quiz_Post_Type();