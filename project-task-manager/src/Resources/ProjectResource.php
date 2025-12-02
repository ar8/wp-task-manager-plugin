<?php
// filepath: project-task-manager/src/Resources/ProjectResource.php

namespace ProjectTaskManager\Resources;

use ProjectTaskManager\Models\Task;

/**
 * ProjectResource
 * 
 * Formats project data for API responses
 */
class ProjectResource {

    /**
     * Transform single project
     * 
     * @param object $project
     * @return array
     */
    public static function make( $project ) {
        return [
            'id'             => (int) $project->id,
            'project_name'   => $project->project_name,
            'description'    => $project->description,
            'start_date'     => $project->start_date,
            'end_date'       => $project->end_date,
            'user_id'        => (int) $project->user_id,
            'task_count'     => Task::getTotalTasksCount( $project->id ),
            'completed_tasks' => Task::getCompletedTasksCount( $project->id ),
            'created_at'     => $project->created_at,
            'updated_at'     => $project->updated_at,
        ];
    }

    /**
     * Transform collection of projects
     * 
     * @param array $projects
     * @return array
     */
    public static function collection( $projects ) {
        return array_map( [ self::class, 'make' ], $projects );
    }

    /**
     * Transform with tasks included
     * 
     * @param object $project
     * @param array  $tasks
     * @return array
     */
    public static function withTasks( $project, $tasks ) {
        $resource = self::make( $project );
        $resource['tasks'] = $tasks;
        return $resource;
    }
}
?>