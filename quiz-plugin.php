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
define('QUIZ_PLUGIN_VERSION', '0.1.0');

/**
 * Autoload the classes
 */
spl_autoload_register(
    function ( $class_name ) {
        $namespace = 'QuizPlugin\\';

        if ( strpos( $class_name, $namespace ) !== 0 ) {
            return;
        }

        $class_path = str_replace( $namespace, '', $class_name );
        $class_path = str_replace( '\\', DIRECTORY_SEPARATOR, $class_path );
        $file       = QUIZ_PLUGIN_PATH . 'includes/' . $class_path . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
);

/**
 * The main plugin class
 */
final class Quiz_Plugin {
    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = '0.1.0';

    /**
     * Class constructor
     */
    private function __construct() {
        $this->define_constants();
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        add_action( 'init', array( $this, 'register_quiz_post_type' ) );
    }

    /**
     * Initialize a singleton instance
     *
     * @return \Quiz_Plugin
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define constants
     */
    private function define_constants() {
        define( 'QUIZ_PLUGIN_FILE', __FILE__ );
        define( 'QUIZ_PLUGIN_ASSETS', QUIZ_PLUGIN_URL . '/build' );
    }

    /**
     * Initialize plugin
     */
    public function init_plugin() {
        new QuizPlugin\API();
    }

    /**
     * Register Quiz Custom Post Type
     */
    public function register_quiz_post_type() {
        register_post_type(
            'quiz',
            array(
                'labels' => array(
                    'name'          => __('Quizzes', 'quiz-plugin'),
                    'singular_name' => __('Quiz', 'quiz-plugin'),
                    'add_new_item'  => __('Add New Quiz', 'quiz-plugin'),
                ),
                'public'       => true,
                'has_archive'  => true,
                'show_in_rest' => true,
                'supports'     => array(
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'custom-fields',
                ),
                'menu_icon'    => 'dashicons-welcome-learn-more',
                'menu_position' => 20,
                'rewrite'      => array('slug' => 'quizzes'),
            )
        );
    }

    /**
     * Activate plugin
     */
    public function activate() {
        $installed = get_option( 'quiz_plugin_installed' );

        if ( ! $installed ) {
            update_option( 'quiz_plugin_installed', time() );
        }

        update_option( 'quiz_plugin_version', QUIZ_PLUGIN_VERSION );

        // Clear permalinks
        flush_rewrite_rules();
    }
}

// Initialize the plugin
Quiz_Plugin::init();

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
            QUIZ_PLUGIN_URL . 'build/quiz/style-index.css',
            array(),
            filemtime(QUIZ_PLUGIN_PATH . 'build/quiz/style-index.css')
        );

        wp_enqueue_script(
            'quiz-plugin-view',
            QUIZ_PLUGIN_URL . 'build/quiz/view.js',
            array('wp-element', 'wp-api-fetch'),
            filemtime(QUIZ_PLUGIN_PATH . 'build/quiz/view.js'),
            true
        );
    }
}
add_action('enqueue_block_assets', 'quiz_plugin_enqueue_scripts');
