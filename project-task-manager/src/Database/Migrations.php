<?php
// filepath: project-task-manager/src/Database/Migrations.php

namespace ProjectTaskManager\Database;

class Migrations {
    
    /**
     * Create plugin tables
     */
    public static function createTables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // ========== PROJECTS TABLE ==========
        $projects_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tm_projects (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            project_name VARCHAR(255) NOT NULL,
            description LONGTEXT NULL,
            start_date DATE NULL,
            end_date DATE NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_created_at (created_at),
            KEY idx_project_name (project_name)
        ) $charset_collate;";

        // ========== TASKS TABLE ==========
        $tasks_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tm_tasks (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            project_id BIGINT(20) UNSIGNED NOT NULL,
            task_name VARCHAR(255) NOT NULL,
            description LONGTEXT NULL,
            priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
            status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
            task_order INT(11) DEFAULT 0,
            due_date DATE NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_project_id (project_id),
            KEY idx_status (status),
            KEY idx_due_date (due_date),
            KEY idx_created_at (created_at),
            CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) 
                REFERENCES {$wpdb->prefix}tm_projects(id) 
                ON DELETE CASCADE
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $projects_table );
        dbDelta( $tasks_table );

        // Log migration completion
        update_option( 'project_task_manager_db_version', PROJECT_TASK_MANAGER_VERSION );
    }

    /**
     * Drop plugin tables
     */
    public static function dropTables() {
        global $wpdb;
        
        // Drop in correct order (foreign key constraints)
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tm_tasks" );
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tm_projects" );

        // Clean up option
        delete_option( 'project_task_manager_db_version' );
    }

    /**
     * Check if tables exist
     */
    public static function tablesExist() {
        global $wpdb;
        
        $projects_table = $wpdb->prefix . 'tm_projects';
        $tasks_table = $wpdb->prefix . 'tm_tasks';
        
        return (
            $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $projects_table ) ) === $projects_table &&
            $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tasks_table ) ) === $tasks_table
        );
    }

    /**
     * Get database version
     */
    public static function getDbVersion() {
        return get_option( 'project_task_manager_db_version' );
    }

    /**
     * Run migrations
     */
    public static function migrate() {
        $current_version = self::getDbVersion();
        
        if ( $current_version === false || version_compare( $current_version, PROJECT_TASK_MANAGER_VERSION, '<' ) ) {
            self::createTables();
        }
    }
}