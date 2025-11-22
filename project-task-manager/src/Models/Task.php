<?php
// filepath: project-task-manager/src/Models/Task.php

namespace TaskManager\Models;

use TaskManager\Models\Project;

/**
 * Task Model Class for WordPress
 * Handles all task-related database operations
 */
class Task {
    
    /**
     * Table name
     */
    private static $table = 'tm_tasks';
    
    /**
     * Fillable attributes
     */
    protected static $fillable = [
        'task_name',
        'description',
        'priority',
        'due_date',
        'project_id',
        'status'
    ];

    /**
     * Get all tasks
     * 
     * @return array Task data
     */
    public static function all() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;
        
        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC" );
    }

    /**
     * Get task by ID
     * 
     * @param int $id Task ID
     * @return object|null Task data
     */
    public static function find( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }

    /**
     * Create new task
     * 
     * @param array $data Task data
     * @return int|false Task ID or false
     */
    public static function create( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;
        
        // Sanitize and validate data
        $task_data = [
            'task_name'   => sanitize_text_field( $data['task_name'] ?? '' ),
            'description' => wp_kses_post( $data['description'] ?? '' ),
            'priority'    => sanitize_text_field( $data['priority'] ?? 'medium' ),
            'due_date'    => $data['due_date'] ?? null,
            'project_id'  => intval( $data['project_id'] ?? 0 ),
            'status'      => sanitize_text_field( $data['status'] ?? 'pending' ),
            'user_id'     => get_current_user_id(),
            'created_at'  => current_time( 'mysql' ),
            'updated_at'  => current_time( 'mysql' ),
        ];

        // Validate required fields
        if ( empty( $task_data['task_name'] ) ) {
            return false;
        }

        // Validate priority
        $valid_priorities = [ 'low', 'medium', 'high' ];
        if ( ! in_array( $task_data['priority'], $valid_priorities ) ) {
            $task_data['priority'] = 'medium';
        }

        // Validate status
        $valid_statuses = [ 'pending', 'in_progress', 'completed' ];
        if ( ! in_array( $task_data['status'], $valid_statuses ) ) {
            $task_data['status'] = 'pending';
        }

        // Validate project exists
        if ( $task_data['project_id'] > 0 ) {
            if ( ! Project::find( $task_data['project_id'] ) ) {
                return false;
            }
        }

        $inserted = $wpdb->insert(
            $table,
            $task_data,
            [ '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' ]
        );

        if ( $inserted ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update task
     * 
     * @param int   $id   Task ID
     * @param array $data Updated data
     * @return int|false Affected rows or false
     */
    public static function update( $id, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;

        // Sanitize data
        $task_data = [];

        if ( isset( $data['task_name'] ) ) {
            $task_data['task_name'] = sanitize_text_field( $data['task_name'] );
        }

        if ( isset( $data['description'] ) ) {
            $task_data['description'] = wp_kses_post( $data['description'] );
        }

        if ( isset( $data['priority'] ) ) {
            $valid_priorities = [ 'low', 'medium', 'high' ];
            $priority = sanitize_text_field( $data['priority'] );
            $task_data['priority'] = in_array( $priority, $valid_priorities ) ? $priority : 'medium';
        }

        if ( isset( $data['due_date'] ) ) {
            $task_data['due_date'] = $data['due_date'];
        }

        if ( isset( $data['status'] ) ) {
            $valid_statuses = [ 'pending', 'in_progress', 'completed' ];
            $status = sanitize_text_field( $data['status'] );
            $task_data['status'] = in_array( $status, $valid_statuses ) ? $status : 'pending';
        }

        $task_data['updated_at'] = current_time( 'mysql' );

        return $wpdb->update(
            $table,
            $task_data,
            [ 'id' => $id ],
            array_fill( 0, count( $task_data ), '%s' ),
            [ '%d' ]
        );
    }

    /**
     * Delete task
     * 
     * @param int $id Task ID
     * @return int|false Affected rows or false
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;

        return $wpdb->delete(
            $table,
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    /**
     * Get tasks by project
     * 
     * @param int $project_id Project ID
     * @return array Tasks data
     */
    public static function getByProject( $project_id ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE project_id = %d ORDER BY created_at DESC",
            $project_id
        ));
    }

    /**
     * Get tasks by user
     * 
     * @param int $user_id User ID
     * @return array Tasks data
     */
    public static function getByUser( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }

    /**
     * Get tasks by status
     * 
     * @param string $status Task status (pending, in_progress, completed)
     * @return array Tasks data
     */
    public static function getByStatus( $status ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;

        $valid_statuses = [ 'pending', 'in_progress', 'completed' ];
        $status = in_array( $status, $valid_statuses ) ? $status : 'pending';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC",
            $status
        ));
    }

    /**
     * Get overdue tasks
     * 
     * @return array Overdue tasks data
     */
    public static function getOverdueTasks() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;
        $current_date = current_time( 'Y-m-d' );

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE due_date < %s 
             AND status != %s 
             ORDER BY due_date ASC",
            $current_date,
            'completed'
        ));
    }

    /**
     * Get high priority tasks
     * 
     * @param int $project_id Optional project ID
     * @return array High priority tasks
     */
    public static function getHighPriorityTasks( $project_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;

        if ( $project_id ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} 
                 WHERE priority = %s 
                 AND project_id = %d 
                 AND status != %s
                 ORDER BY due_date ASC",
                'high',
                $project_id,
                'completed'
            ));
        }

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE priority = %s 
             AND status != %s
             ORDER BY due_date ASC",
            'high',
            'completed'
        ));
    }

    /**
     * Check if task is overdue
     * 
     * @param int $task_id Task ID
     * @return bool
     */
    public static function isOverdue( $task_id ) {
        $task = self::find( $task_id );
        
        if ( ! $task || ! $task->due_date || $task->status === 'completed' ) {
            return false;
        }

        $due_date = strtotime( $task->due_date );
        $current_date = current_time( 'timestamp' );

        return $due_date < $current_date;
    }

    /**
     * Check if task is high priority
     * 
     * @param int $task_id Task ID
     * @return bool
     */
    public static function isHighPriority( $task_id ) {
        $task = self::find( $task_id );
        
        return $task && $task->priority === 'high';
    }

    /**
     * Check if task is completed
     * 
     * @param int $task_id Task ID
     * @return bool
     */
    public static function isCompleted( $task_id ) {
        $task = self::find( $task_id );
        
        return $task && $task->status === 'completed';
    }

    /**
     * Mark task as completed
     * 
     * @param int $task_id Task ID
     * @return bool
     */
    public static function markCompleted( $task_id ) {
        return self::update( $task_id, [ 'status' => 'completed' ] );
    }

    /**
     * Mark task as in progress
     * 
     * @param int $task_id Task ID
     * @return bool
     */
    public static function markInProgress( $task_id ) {
        return self::update( $task_id, [ 'status' => 'in_progress' ] );
    }

    /**
     * Mark task as pending
     * 
     * @param int $task_id Task ID
     * @return bool
     */
    public static function markPending( $task_id ) {
        return self::update( $task_id, [ 'status' => 'pending' ] );
    }

    /**
     * Get task with project details
     * 
     * @param int $task_id Task ID
     * @return array Task data with project info
     */
    public static function withProject( $task_id ) {
        global $wpdb;
        $task_table = $wpdb->prefix . self::$table;
        $project_table = $wpdb->prefix . 'tm_projects';

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT t.*, p.project_name, p.description as project_description 
             FROM {$task_table} t
             LEFT JOIN {$project_table} p ON t.project_id = p.id
             WHERE t.id = %d",
            $task_id
        ));
    }

    /**
     * Get task statistics
     * 
     * @param int $project_id Optional project ID
     * @return array Statistics
     */
    public static function getStats( $project_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;

        if ( $project_id ) {
            $where = $wpdb->prepare( "WHERE project_id = %d", $project_id );
        } else {
            $where = '';
        }

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );
        $completed = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where} AND status = 'completed'" );
        $overdue = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} {$where} AND due_date < %s AND status != %s",
            current_time( 'Y-m-d' ),
            'completed'
        ));
        $high_priority = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where} AND priority = 'high' AND status != 'completed'" );

        $progress = $total > 0 ? ( $completed / $total ) * 100 : 0;

        return [
            'total_tasks'       => $total,
            'completed_tasks'   => $completed,
            'pending_tasks'     => $total - $completed,
            'overdue_tasks'     => $overdue,
            'high_priority'     => $high_priority,
            'progress'          => round( $progress, 2 ),
        ];
    }

    /**
     * Search tasks
     * 
     * @param string $search Search term
     * @param int    $project_id Optional project ID
     * @return array Search results
     */
    public static function search( $search, $project_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table;
        $search_term = '%' . $wpdb->esc_like( $search ) . '%';

        if ( $project_id ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} 
                 WHERE (task_name LIKE %s OR description LIKE %s)
                 AND project_id = %d
                 ORDER BY created_at DESC",
                $search_term,
                $search_term,
                $project_id
            ));
        }

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE task_name LIKE %s OR description LIKE %s
             ORDER BY created_at DESC",
            $search_term,
            $search_term
        ));
    }
}
?>