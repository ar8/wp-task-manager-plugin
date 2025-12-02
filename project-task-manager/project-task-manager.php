<?php
/**
 * Plugin Name: Project Task Manager
 * Plugin URI: https://github.com/ar8/wp-task-manager-plugin
 * Description: A drag-and-drop project task management system for WordPress
 * Version: 1.0.0
 * Author: Ana Rodriguez
 * Author URI: https://anarodriguez.dev/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: project-task-manager
 * Domain Path: /languages
 */

// __define-ocg__ - Plugin initialization and constant definitions

// Step 1: Define plugin constants FIRST
if (!defined('PROJECT_TASK_MANAGER_VERSION')) {
    define('PROJECT_TASK_MANAGER_VERSION', '1.0.0');
}

if (!defined('PROJECT_TASK_MANAGER_PLUGIN_PATH')) {
    define('PROJECT_TASK_MANAGER_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('PROJECT_TASK_MANAGER_PLUGIN_URL')) {
    define('PROJECT_TASK_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
}

error_log('=== PROJECT TASK MANAGER PLUGIN LOADING ===');
error_log('Version: ' . PROJECT_TASK_MANAGER_VERSION);
error_log('Path: ' . PROJECT_TASK_MANAGER_PLUGIN_PATH);

// Step 2: Load the install file BEFORE registering hooks
$install_file = PROJECT_TASK_MANAGER_PLUGIN_PATH . 'includes/install.php';
error_log('Looking for install file: ' . $install_file);

if (file_exists($install_file)) {
    require_once $install_file;
    error_log('✅ Install file loaded successfully');
} else {
    error_log('❌ Install file NOT found!');
    wp_die('Project Task Manager: install.php file not found!');
}

// Verify the function exists
if (!function_exists('project_task_manager_install')) {
    error_log('❌ CRITICAL: project_task_manager_install function not found!');
    wp_die('Project Task Manager: project_task_manager_install() function not defined!');
} else {
    error_log('✅ project_task_manager_install function found');
}

// Step 3: Load other required files
$varOcg = [
    'includes/deactivation.php',
    'includes/helpers.php',
];

$varFiltersCg = [];

foreach ($varOcg as $file) {
    $file_path = PROJECT_TASK_MANAGER_PLUGIN_PATH . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
        $varFiltersCg[] = $file;
        error_log('✅ Loaded: ' . $file);
    } else {
        error_log('⚠️ Optional file not found: ' . $file);
    }
}

// Step 4: Register activation hook using CORRECT function name
error_log('Registering activation hook with: project_task_manager_install');
register_activation_hook(__FILE__, 'project_task_manager_install');

// Step 5: Register deactivation hook
error_log('Registering deactivation hook with: project_task_manager_deactivate');
register_deactivation_hook(__FILE__, 'project_task_manager_deactivate');

error_log('=== PROJECT TASK MANAGER PLUGIN LOADED SUCCESSFULLY ===');


// ============ ADMIN MENU ============
/**
 * __define-ocg__ - Register admin menu for Task Manager
 * varOcg: Add main menu page
 * varFiltersCg: Load dashboard view
 */
add_action('admin_menu', function() {
    add_menu_page(
        __('Project Task Manager', 'project-task-manager'),
        __('Tasks', 'project-task-manager'),
        'manage_options',
        'project-task-manager',
        function() {
            if (file_exists(PROJECT_TASK_MANAGER_PLUGIN_PATH . 'src/Views/dashboard.php')) {
                include PROJECT_TASK_MANAGER_PLUGIN_PATH . 'src/Views/dashboard.php';
            } else {
                echo '<div class="wrap"><h1>Dashboard not found</h1></div>';
            }
        },
        'dashicons-checklist',
        6
    );
});


// ============ ENQUEUE SCRIPTS & STYLES ============
/**
 * __define-ocg__ - Load CSS and JavaScript assets for plugin
 * varOcg: Enqueue external libraries (Bootstrap, Font Awesome, Sortable)
 * varFiltersCg: Enqueue plugin-specific CSS and JS
 */
add_action('admin_enqueue_scripts', function($hook) {
    // Only load on plugin pages
    if (strpos($hook, 'project-task-manager') === false) {
        return;
    }

    error_log('Enqueuing assets on page: ' . $hook);

    // ============ STYLES ============
    
    // Bootstrap CSS
    wp_enqueue_style(
        'bootstrap-css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        [],
        '5.3.0'
    );

    // Font Awesome CSS
    wp_enqueue_style(
        'font-awesome-css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
        [],
        '6.0.0'
    );

    // Plugin CSS
    wp_enqueue_style(
        'project-task-manager-css',
        PROJECT_TASK_MANAGER_PLUGIN_URL . 'assets/css/project-task-manager.css',
        ['bootstrap-css'],
        PROJECT_TASK_MANAGER_VERSION
    );

    // ============ SCRIPTS ============
    
    // Sortable.js - for drag and drop
    wp_enqueue_script(
        'sortablejs',
        'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js',
        [],
        '1.15.0',
        true
    );

    // Bootstrap JS
    wp_enqueue_script(
        'bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
        [],
        '5.3.0',
        true
    );

    // Plugin JS
    wp_enqueue_script(
        'project-task-manager-js',
        PROJECT_TASK_MANAGER_PLUGIN_URL . 'assets/js/project-task-manager.js',
        ['sortablejs', 'bootstrap-js'],
        PROJECT_TASK_MANAGER_VERSION,
        true
    );

    // ============ LOCALIZE SCRIPT (Pass PHP data to JavaScript) ============
    
    // API endpoint
    wp_localize_script(
        'project-task-manager-js',
        'projectTaskManagerAPI',
        rest_url('project-task-manager/v1')
    );

    // Security nonce
    wp_localize_script(
        'project-task-manager-js',
        'projectTaskManagerNonce',
        wp_create_nonce('project_task_manager_nonce')
    );

    // Internationalization strings
    wp_localize_script(
        'project-task-manager-js',
        'projectTaskManagerI18n',
        [
            'allProjects'           => __('All Projects', 'project-task-manager'),
            'selectProjectOptional' => __('Select Project (Optional)', 'project-task-manager'),
            'taskCreated'           => __('Task created successfully', 'project-task-manager'),
            'projectCreated'        => __('Project created successfully', 'project-task-manager'),
            'taskUpdated'           => __('Task updated successfully', 'project-task-manager'),
            'projectUpdated'        => __('Project updated successfully', 'project-task-manager'),
            'taskDeleted'           => __('Task deleted successfully', 'project-task-manager'),
            'projectDeleted'        => __('Project deleted successfully', 'project-task-manager'),
            'deleteConfirmTask'     => __('Are you sure you want to delete this task?', 'project-task-manager'),
            'deleteConfirmProject'  => __('Are you sure you want to delete this project?', 'project-task-manager'),
        ]
    );
});


// ============ REST API ROUTES ============
/**
 * __define-ocg__ - Register REST API endpoints
 * varOcg: Hook into rest_api_init
 * varFiltersCg: Allow routes to be registered via action hook
 */
add_action('rest_api_init', function() {
    do_action('task_manager_register_routes');
});

// NO CLOSING PHP TAG - prevents whitespace output
