<?php
// filepath: /project-task-manager/includes/install.php

namespace TaskManager\Includes;
use TaskManager\Database\Migrations;

/**
 * Run plugin installation
 */
function task_manager_install() {
    // Check if user can manage options (security check)
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Create tables
    Migrations::migrate();

    // Set up default options if needed
    if ( ! get_option( 'task_manager_settings' ) ) {
        update_option( 'task_manager_settings', [
            'version' => TASK_MANAGER_VERSION,
            'installed' => current_time( 'mysql' ),
        ]);
    }

    // Flush rewrite rules
    flush_rewrite_rules();

    // Log installation
    error_log( 'Task Manager plugin installed successfully' );
}

/**
 * Run plugin uninstall
 */
function task_manager_uninstall() {
    // Check if user can manage options
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Drop tables
    Migrations::dropTables();

    // Remove options
    delete_option( 'task_manager_settings' );
    delete_option( 'task_manager_db_version' );

    // Flush rewrite rules
    flush_rewrite_rules();

    // Log uninstall
    error_log( 'Task Manager plugin uninstalled' );
}