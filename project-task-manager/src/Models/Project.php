<?php
// filepath: project-task-manager/src/Models/Project.php

namespace ProjectTaskManager\Models;

/**
 * Project Model Class for WordPress
 * Handles all project-related database operations
 */
class Project {
    
    /**
     * Table name
     */
    private $table = 'tm_projects';
    
    /**
     * Fillable attributes (fields that can be mass assigned)
     */
    protected $fillable = [
        'project_name',
        'description',
        'start_date',
        'end_date',
        'user_id'
    ];

    /**
     * Get all projects
     * 
     * @return array Project data
     */
    public static function all() {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_projects';
        
        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );
    }

    /**
     * Get project by ID
     * 
     * @param int $id Project ID
     * @return object|null Project data
     */
    public static function find( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_projects';
        
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
    }

    /**
     * Create new project
     * 
     * @param array $data Project data
     * @return int|false Project ID or false
     */
    public static function create( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_projects';
        
        // Sanitize and validate data
        $project_data = [
            'project_name' => sanitize_text_field( $data['project_name'] ?? '' ),
            'description'  => wp_kses_post( $data['description'] ?? '' ),
            'start_date'   => $data['start_date'] ?? null,
            'end_date'     => $data['end_date'] ?? null,
            'user_id'      => get_current_user_id(),
            'created_at'   => current_time( 'mysql' ),
            'updated_at'   => current_time( 'mysql' ),
        ];

        // Remove null values
        $project_data = array_filter( $project_data, function( $value ) {
            return $value !== null;
        });

        $inserted = $wpdb->insert(
            $table,
            $project_data,
            [ '%s', '%s', '%s', '%s', '%d', '%s', '%s' ]
        );

        if ( $inserted ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update project
     * 
     * @param int   $id   Project ID
     * @param array $data Updated data
     * @return int|false Affected rows or false
     */
    public static function update( $id, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_projects';

        // Sanitize data
        $project_data = [
            'project_name' => sanitize_text_field( $data['project_name'] ?? '' ),
            'description'  => wp_kses_post( $data['description'] ?? '' ),
            'start_date'   => $data['start_date'] ?? null,
            'end_date'     => $data['end_date'] ?? null,
            'updated_at'   => current_time( 'mysql' ),
        ];

        return $wpdb->update(
            $table,
            $project_data,
            [ 'id' => $id ],
            [ '%s', '%s', '%s', '%s', '%s' ],
            [ '%d' ]
        );
    }

    /**
     * Delete project
     * 
     * @param int $id Project ID
     * @return int|false Affected rows or false
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_projects';

        return $wpdb->delete(
            $table,
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    /**
     * Get all tasks for this project
     * 
     * @param int $project_id Project ID
     * @return array Task data
     */
    public static function getTasks( $project_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_tasks';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE project_id = %d ORDER BY created_at DESC",
            $project_id
        ));
    }

    /**
     * Get completed tasks count
     * 
     * @param int $project_id Project ID
     * @return int Count of completed tasks
     */
    public static function getCompletedTasksCount( $project_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_tasks';

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE project_id = %d AND status = 'completed'",
            $project_id
        ));
    }

    /**
     * Get total tasks count
     * 
     * @param int $project_id Project ID
     * @return int Total tasks count
     */
    public static function getTotalTasksCount( $project_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_tasks';

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE project_id = %d",
            $project_id
        ));
    }

    /**
     * Check if project is overdue
     * 
     * @param int $project_id Project ID
     * @return bool
     */
    public static function isOverdue( $project_id ) {
        $project = self::find( $project_id );
        
        if ( ! $project || ! $project->end_date ) {
            return false;
        }

        $end_date = strtotime( $project->end_date );
        return $end_date < current_time( 'timestamp' );
    }

    /**
     * Get projects by user
     * 
     * @param int $user_id User ID
     * @return array Projects data
     */
    public static function getByUser( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tm_projects';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }

    /**
     * Get project statistics
     * 
     * @param int $project_id Project ID
     * @return array Statistics
     */
    public static function getStats( $project_id ) {
        $total = self::getTotalTasksCount( $project_id );
        $completed = self::getCompletedTasksCount( $project_id );
        $progress = $total > 0 ? ( $completed / $total ) * 100 : 0;

        return [
            'total_tasks'      => $total,
            'completed_tasks'  => $completed,
            'pending_tasks'    => $total - $completed,
            'progress'         => round( $progress, 2 ),
            'is_overdue'       => self::isOverdue( $project_id ),
        ];
    }
}