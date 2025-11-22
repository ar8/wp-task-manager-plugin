<?php
// filepath: /project-task-manager/includes/routes.php

namespace TaskManager\Routes;

use TaskManager\Controllers\ProjectController;
use TaskManager\Controllers\TaskController;

/**
 * Register all plugin REST API routes
 */
class Routes {
    
    /**
     * Initialize routes
     */
    public static function init() {
        add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
    }

    /**
     * Register REST API routes
     */
    public static function register_routes() {
        
        // ========== PROJECT ROUTES ==========
        
        // GET /wp-json/project-task-manager/v1/projects
        register_rest_route(
            'project-task-manager/v1',
            '/projects',
            [
                'methods'             => 'GET',
                'callback'            => [ ProjectController::class, 'index' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
                'args'                => [
                    'page'     => [
                        'type'    => 'integer',
                        'default' => 1,
                    ],
                    'per_page' => [
                        'type'    => 'integer',
                        'default' => 10,
                    ],
                ],
            ]
        );

        // POST /wp-json/project-task-manager/v1/projects
        register_rest_route(
            'project-task-manager/v1',
            '/projects',
            [
                'methods'             => 'POST',
                'callback'            => [ ProjectController::class, 'store' ],
                'permission_callback' => [ self::class, 'check_write_permission' ],
                'args'                => [
                    'project_name' => [
                        'type'     => 'string',
                        'required' => true,
                    ],
                    'description'  => [
                        'type' => 'string',
                    ],
                    'start_date'   => [
                        'type' => 'string',
                        'format' => 'date',
                    ],
                    'end_date'     => [
                        'type' => 'string',
                        'format' => 'date',
                    ],
                ],
            ]
        );

        // GET /wp-json/project-task-manager/v1/projects/{id}
        register_rest_route(
            'project-task-manager/v1',
            '/projects/(?P<id>\d+)',
            [
                'methods'             => 'GET',
                'callback'            => [ ProjectController::class, 'show' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
                'args'                => [
                    'id' => [
                        'type'     => 'integer',
                        'required' => true,
                    ],
                ],
            ]
        );

        // PUT/PATCH /wp-json/project-task-manager/v1/projects/{id}
        register_rest_route(
            'project-task-manager/v1',
            '/projects/(?P<id>\d+)',
            [
                'methods'             => [ 'PUT', 'PATCH' ],
                'callback'            => [ ProjectController::class, 'update' ],
                'permission_callback' => [ self::class, 'check_write_permission' ],
                'args'                => [
                    'id'             => [
                        'type'     => 'integer',
                        'required' => true,
                    ],
                    'project_name'   => [
                        'type' => 'string',
                    ],
                    'description'    => [
                        'type' => 'string',
                    ],
                    'start_date'     => [
                        'type' => 'string',
                    ],
                    'end_date'       => [
                        'type' => 'string',
                    ],
                ],
            ]
        );

        // DELETE /wp-json/project-task-manager/v1/projects/{id}
        register_rest_route(
            'project-task-manager/v1',
            '/projects/(?P<id>\d+)',
            [
                'methods'             => 'DELETE',
                'callback'            => [ ProjectController::class, 'destroy' ],
                'permission_callback' => [ self::class, 'check_write_permission' ],
                'args'                => [
                    'id' => [
                        'type'     => 'integer',
                        'required' => true,
                    ],
                ],
            ]
        );

        // ========== TASK ROUTES ==========

        // GET /wp-json/project-task-manager/v1/tasks
        register_rest_route(
            'project-task-manager/v1',
            '/tasks',
            [
                'methods'             => 'GET',
                'callback'            => [ TaskController::class, 'index' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
                'args'                => [
                    'page'       => [
                        'type'    => 'integer',
                        'default' => 1,
                    ],
                    'per_page'   => [
                        'type'    => 'integer',
                        'default' => 10,
                    ],
                    'project_id' => [
                        'type' => 'integer',
                    ],
                    'status'     => [
                        'type' => 'string',
                        'enum' => [ 'pending', 'in_progress', 'completed' ],
                    ],
                ],
            ]
        );

        // POST /wp-json/project-task-manager/v1/tasks
        register_rest_route(
            'project-task-manager/v1',
            '/tasks',
            [
                'methods'             => 'POST',
                'callback'            => [ TaskController::class, 'store' ],
                'permission_callback' => [ self::class, 'check_write_permission' ],
                'args'                => [
                    'task_name'   => [
                        'type'     => 'string',
                        'required' => true,
                    ],
                    'description' => [
                        'type' => 'string',
                    ],
                    'priority'    => [
                        'type' => 'string',
                        'enum' => [ 'low', 'medium', 'high' ],
                    ],
                    'due_date'    => [
                        'type' => 'string',
                        'format' => 'date',
                    ],
                    'project_id'  => [
                        'type' => 'integer',
                    ],
                    'status'      => [
                        'type' => 'string',
                        'enum' => [ 'pending', 'in_progress', 'completed' ],
                    ],
                ],
            ]
        );

        // GET /wp-json/project-task-manager/v1/tasks/{id}
        register_rest_route(
            'project-task-manager/v1',
            '/tasks/(?P<id>\d+)',
            [
                'methods'             => 'GET',
                'callback'            => [ TaskController::class, 'show' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
                'args'                => [
                    'id' => [
                        'type'     => 'integer',
                        'required' => true,
                    ],
                ],
            ]
        );

        // PUT/PATCH /wp-json/project-task-manager/v1/tasks/{id}
        register_rest_route(
            'project-task-manager/v1',
            '/tasks/(?P<id>\d+)',
            [
                'methods'             => [ 'PUT', 'PATCH' ],
                'callback'            => [ TaskController::class, 'update' ],
                'permission_callback' => [ self::class, 'check_write_permission' ],
                'args'                => [
                    'id'          => [
                        'type'     => 'integer',
                        'required' => true,
                    ],
                    'task_name'   => [
                        'type' => 'string',
                    ],
                    'description' => [
                        'type' => 'string',
                    ],
                    'priority'    => [
                        'type' => 'string',
                    ],
                    'due_date'    => [
                        'type' => 'string',
                    ],
                    'project_id'  => [
                        'type' => 'integer',
                    ],
                    'status'      => [
                        'type' => 'string',
                    ],
                ],
            ]
        );

        // DELETE /wp-json/project-task-manager/v1/tasks/{id}
        register_rest_route(
            'project-task-manager/v1',
            '/tasks/(?P<id>\d+)',
            [
                'methods'             => 'DELETE',
                'callback'            => [ TaskController::class, 'destroy' ],
                'permission_callback' => [ self::class, 'check_write_permission' ],
                'args'                => [
                    'id' => [
                        'type'     => 'integer',
                        'required' => true,
                    ],
                ],
            ]
        );

        // ========== CUSTOM TASK ROUTES ==========

        // GET /wp-json/project-task-manager/v1/tasks/by-project/{project_id}
        register_rest_route(
            'project-task-manager/v1',
            '/tasks/by-project/(?P<project_id>\d+)',
            [
                'methods'             => 'GET',
                'callback'            => [ TaskController::class, 'get_by_project' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
                'args'                => [
                    'project_id' => [
                        'type'     => 'integer',
                        'required' => true,
                    ],
                ],
            ]
        );

        // GET /wp-json/project-task-manager/v1/tasks/projects-dropdown
        register_rest_route(
            'project-task-manager/v1',
            '/tasks/projects-dropdown',
            [
                'methods'             => 'GET',
                'callback'            => [ TaskController::class, 'get_projects' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
            ]
        );

        // GET /wp-json/project-task-manager/v1/tasks/overdue
        register_rest_route(
            'project-task-manager/v1',
            '/tasks/overdue',
            [
                'methods'             => 'GET',
                'callback'            => [ TaskController::class, 'get_overdue' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
            ]
        );

        // GET /wp-json/project-task-manager/v1/tasks/high-priority
        register_rest_route(
            'project-task-manager/v1',
            '/tasks/high-priority',
            [
                'methods'             => 'GET',
                'callback'            => [ TaskController::class, 'get_high_priority' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
            ]
        );

        // GET /wp-json/project-task-manager/v1/tasks/stats
        register_rest_route(
            'project-task-manager/v1',
            '/tasks/stats',
            [
                'methods'             => 'GET',
                'callback'            => [ TaskController::class, 'get_stats' ],
                'permission_callback' => [ self::class, 'check_read_permission' ],
            ]
        );

        // ========== TEST ROUTE ==========

        // GET /wp-json/project-task-manager/v1/test
        register_rest_route(
            'project-task-manager/v1',
            '/test',
            [
                'methods'             => 'GET',
                'callback'            => function() {
                    return rest_ensure_response( [
                        'message' => 'Test route works',
                    ]);
                },
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Check if user has read permission
     * 
     * @return bool
     */
    public static function check_read_permission() {
        return current_user_can( 'read' );
    }

    /**
     * Check if user has write permission
     * 
     * @return bool
     */
    public static function check_write_permission() {
        return current_user_can( 'edit_posts' );
    }
}

// Initialize routes
Routes::init();
?>






