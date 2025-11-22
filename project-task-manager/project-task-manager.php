<?php
/**
 * Plugin Name: Project Task Manager
 * Plugin URI: https://github.com/ar8/wp--plugin
 * Description: A drag-and-drop task management system for WordPress
 * Version: 1.0.0
 * Author: Ana Rodriguez
 * Author URI: https://anarodriguez.dev/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: project-task-manager
 * Domain Path: /languages
 */

// Security: Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'TASK_MANAGER_VERSION', '1.0.0' );
define( 'TASK_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'TASK_MANAGER_URL', plugin_dir_url( __FILE__ ) );
define( 'TASK_MANAGER_BASENAME', plugin_basename( __FILE__ ) );

// ============ INCLUDE REQUIRED FILES ============

// Autoload classes
require_once TASK_MANAGER_PATH . 'src/Database/Migrations.php';

// Helper functions
if ( file_exists( TASK_MANAGER_PATH . 'includes/helpers.php' ) ) {
    require_once TASK_MANAGER_PATH . 'includes/helpers.php';
}

// Installation
require_once TASK_MANAGER_PATH . 'includes/install.php';

// Models
if ( file_exists( TASK_MANAGER_PATH . 'includes/models/Project.php' ) ) {
    require_once TASK_MANAGER_PATH . 'includes/models/Project.php';
}
if ( file_exists( TASK_MANAGER_PATH . 'includes/models/Task.php' ) ) {
    require_once TASK_MANAGER_PATH . 'includes/models/Task.php';
}

// Controllers
if ( file_exists( TASK_MANAGER_PATH . 'includes/controllers/ProjectController.php' ) ) {
    require_once TASK_MANAGER_PATH . 'includes/controllers/ProjectController.php';
}
if ( file_exists( TASK_MANAGER_PATH . 'includes/controllers/TaskController.php' ) ) {
    require_once TASK_MANAGER_PATH . 'includes/controllers/TaskController.php';
}

// REST API routes
if ( file_exists( TASK_MANAGER_PATH . 'includes/routes.php' ) ) {
    require_once TASK_MANAGER_PATH . 'includes/routes.php';
}

// ============ PLUGIN ACTIVATION/DEACTIVATION ============

/**
 * Plugin activation hook
 */
register_activation_hook( __FILE__, 'task_manager_install' );

/**
 * Plugin deactivation hook
 */
register_deactivation_hook( __FILE__, function() {
    // Clean up transients
    delete_transient( 'task_manager_setup_complete' );
    flush_rewrite_rules();
});

/**
 * Plugin uninstall hook
 */
register_uninstall_hook( __FILE__, 'task_manager_uninstall' );

// ============ PLUGIN INITIALIZATION ============

/**
 * Initialize the plugin
 */
add_action( 'plugins_loaded', function() {
    // Load plugin text domain
    load_plugin_textdomain(
        'project-task-manager',
        false,
        dirname( TASK_MANAGER_BASENAME ) . '/languages'
    );

    // Check if database is set up
    if ( ! \TaskManager\Database\Migrations::tablesExist() ) {
        \TaskManager\Database\Migrations::migrate();
    }
});

// ============ ADMIN MENU ============

add_action( 'admin_menu', function() {
    add_menu_page(
        __( 'Task Manager', 'project-task-manager' ),
        __( 'Tasks', 'project-task-manager' ),
        'manage_options',
        'project-task-manager',
        function() {
            include TASK_MANAGER_PATH . 'src/Views/dashboard.php';
        },
        'dashicons-checklist',
        6
    );
});

// ============ ENQUEUE SCRIPTS & STYLES ============

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( strpos( $hook, 'project-task-manager' ) === false ) {
        return;
    }

    // Styles
    wp_enqueue_style(
        'bootstrap-css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        [],
        '5.3.0'
    );

    wp_enqueue_style(
        'font-awesome-css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
        [],
        '6.0.0'
    );

    wp_enqueue_style(
        'task-manager-css',
        TASK_MANAGER_URL . 'assets/css/project-task-manager.css',
        [ 'bootstrap-css' ],
        TASK_MANAGER_VERSION
    );

    // Scripts
    wp_enqueue_script(
        'sortablejs',
        'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js',
        [],
        '1.15.0',
        true
    );

    wp_enqueue_script(
        'bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
        [],
        '5.3.0',
        true
    );

    wp_enqueue_script(
        'task-manager-js',
        TASK_MANAGER_URL . 'assets/js/project-task-manager.js',
        [ 'sortablejs', 'bootstrap-js' ],
        TASK_MANAGER_VERSION,
        true
    );

    // Localize script
    wp_localize_script(
        'task-manager-js',
        'taskManagerAPI',
        rest_url( 'project-task-manager/v1' )
    );

    wp_localize_script(
        'project-task-manager-js',
        'projectTaskManagerNonce',
        wp_create_nonce( 'project_task_manager_nonce' )
    );

    wp_localize_script(
        'project-task-manager-js',
        'projectTaskManagerI18n',
        [
            'allProjects'              => __( 'All Projects', 'project-task-manager' ),
            'selectProjectOptional'    => __( 'Select Project (Optional)', 'project-task-manager' ),
            'taskCreated'              => __( 'Task created successfully', 'project-task-manager' ),
            'projectCreated'           => __( 'Project created successfully', 'project-task-manager' ),
            'taskUpdated'              => __( 'Task updated successfully', 'project-task-manager' ),
            'projectUpdated'           => __( 'Project updated successfully', 'project-task-manager' ),
            'taskDeleted'              => __( 'Task deleted successfully', 'project-task-manager' ),
            'projectDeleted'           => __( 'Project deleted successfully', 'project-task-manager' ),
            'deleteConfirmTask'        => __( 'Are you sure you want to delete this task?', 'project-task-manager' ),
            'deleteConfirmProject'     => __( 'Are you sure you want to delete this project?', 'project-task-manager' ),
        ]
    );
});

// ============ REST API ============

add_action( 'rest_api_init', function() {
    do_action( 'task_manager_register_routes' );
});

