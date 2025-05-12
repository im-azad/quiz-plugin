<?php
/**
 * Plugin Name:       Quiz Plugin
 * Description:       A React-based quiz plugin with Gutenberg block integration.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quiz-plugin
 *
 * @package QuizPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define('QUIZ_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('QUIZ_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once QUIZ_PLUGIN_PATH . 'includes/class-quiz-post-type.php';
require_once QUIZ_PLUGIN_PATH . 'includes/class-quiz-rest-controller.php';

/**
 * Initialize the plugin
 */
function quiz_plugin_init() {
    // Initialize REST API controller
    $rest_controller = new Quiz_REST_Controller();
    $rest_controller->register_routes();
}
add_action('rest_api_init', 'quiz_plugin_init');

/**
 * Register block assets
 */
function quiz_plugin_register_block() {
    /**
     * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
     * based on the registered block metadata.
     */
    if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
        wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
        return;
    }

    if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
        wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
    }

    $manifest_data = require __DIR__ . '/build/blocks-manifest.php';
    foreach ( array_keys( $manifest_data ) as $block_type ) {
        register_block_type( __DIR__ . "/build/{$block_type}", array(
            'attributes' => array(
                'quizId' => array(
                    'type' => 'number',
                    'default' => 0
                )
            )
        ));
    }
}
add_action('init', 'quiz_plugin_register_block');

/**
 * Enqueue frontend scripts
 */
function quiz_plugin_enqueue_scripts() {
    if (has_block('create-block/quiz-plugin')) {
        wp_enqueue_style(
            'quiz-plugin-style',
            QUIZ_PLUGIN_URL . 'build/quiz-plugin/style-index.css',
            array(),
            filemtime(QUIZ_PLUGIN_PATH . 'build/quiz-plugin/style-index.css')
        );

        wp_enqueue_script(
            'quiz-plugin-view',
            QUIZ_PLUGIN_URL . 'build/quiz-plugin/view.js',
            array('wp-element', 'wp-api-fetch'),
            filemtime(QUIZ_PLUGIN_PATH . 'build/quiz-plugin/view.js'),
            true
        );
    }
}
add_action('enqueue_block_assets', 'quiz_plugin_enqueue_scripts');
