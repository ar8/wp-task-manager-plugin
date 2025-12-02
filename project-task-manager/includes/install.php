<?php
// filepath: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/project-task-manager/includes/install.php

/**
 * __define-ocg__ - Plugin installation function
 */
function project_task_manager_install() {
    error_log('=== PROJECT TASK MANAGER ACTIVATION STARTED ===');
    error_log('Plugin Path: ' . PROJECT_TASK_MANAGER_PLUGIN_PATH);
    error_log('Plugin URL: ' . PROJECT_TASK_MANAGER_PLUGIN_URL);
    error_log('Plugin Version: ' . PROJECT_TASK_MANAGER_VERSION);
    
    // varOcg: Verify constants are defined
    $varOcg = [
        'PROJECT_TASK_MANAGER_VERSION' => defined('PROJECT_TASK_MANAGER_VERSION'),
        'PROJECT_TASK_MANAGER_PLUGIN_PATH' => defined('PROJECT_TASK_MANAGER_PLUGIN_PATH'),
        'PROJECT_TASK_MANAGER_PLUGIN_URL' => defined('PROJECT_TASK_MANAGER_PLUGIN_URL')
    ];
    
    error_log('Constants defined: ' . json_encode($varOcg));
    
    // varFiltersCg: Load migration class
    $varFiltersCg = [];
    
    // Load Migrations class
    $migrations_file = PROJECT_TASK_MANAGER_PLUGIN_PATH . 'src/Database/Migrations.php';
    if (file_exists($migrations_file)) {
        require_once $migrations_file;
        error_log('Migrations class file loaded');
    }
    
    // Check if Migrations class exists
    if (class_exists('ProjectTaskManager\Database\Migrations')) {
        error_log('Migrations class found');
        
        // Run migrations
        try {
            \ProjectTaskManager\Database\Migrations::migrate();
            $varFiltersCg['migration'] = 'success';
            error_log('Database tables created');
            
            // Flush rewrite rules
            flush_rewrite_rules();
            error_log('Rewrite rules flushed');
            
        } catch (\Exception $e) {
            error_log('Migration error: ' . $e->getMessage());
            $varFiltersCg['migration'] = 'error: ' . $e->getMessage();
        }
    } else {
        error_log('ERROR: Migrations class not found!');
        $varFiltersCg['migration'] = 'Class not found';
    }
    
    error_log('Installation results: ' . json_encode($varFiltersCg));
    error_log('=== PROJECT TASK MANAGER ACTIVATION COMPLETED ===');
}
// NO CLOSING PHP TAG - Prevents whitespace output