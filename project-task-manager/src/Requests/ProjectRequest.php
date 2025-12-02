<?php
// filepath: project-task-manager/src/Requests/ProjectRequest.php

namespace ProjectTaskManager\Requests;

/**
 * ProjectRequest
 * 
 * Handles validation for project requests
 */
class ProjectRequest {

    /**
     * Validate store (create) request
     * 
     * @param array $data
     * @return array|array Validated data with errors
     */
    public static function validateStore( $data ) {
        $errors = [];
        $validated = [];

        // project_name - required, string, max 255
        if ( empty( $data['project_name'] ?? '' ) ) {
            $errors['project_name'] = 'Project name is required';
        } elseif ( strlen( $data['project_name'] ) > 255 ) {
            $errors['project_name'] = 'Project name must not exceed 255 characters';
        } else {
            $validated['project_name'] = sanitize_text_field( $data['project_name'] );
        }

        // description - optional, string
        if ( isset( $data['description'] ) ) {
            if ( ! is_string( $data['description'] ) ) {
                $errors['description'] = 'Description must be a string';
            } else {
                $validated['description'] = wp_kses_post( $data['description'] );
            }
        }

        // start_date - optional, date
        if ( isset( $data['start_date'] ) ) {
            if ( ! self::isValidDate( $data['start_date'] ) ) {
                $errors['start_date'] = 'Start date must be a valid date (YYYY-MM-DD)';
            } else {
                $validated['start_date'] = $data['start_date'];
            }
        }

        // end_date - optional, date, after_or_equal start_date
        if ( isset( $data['end_date'] ) ) {
            if ( ! self::isValidDate( $data['end_date'] ) ) {
                $errors['end_date'] = 'End date must be a valid date (YYYY-MM-DD)';
            } elseif ( 
                isset( $validated['start_date'] ) && 
                strtotime( $data['end_date'] ) < strtotime( $validated['start_date'] )
            ) {
                $errors['end_date'] = 'End date must be after or equal to start date';
            } else {
                $validated['end_date'] = $data['end_date'];
            }
        }

        return [
            'errors'    => $errors,
            'validated' => $validated,
            'passes'    => empty( $errors ),
        ];
    }

    /**
     * Validate update request
     * 
     * @param array $data
     * @return array Validated data with errors
     */
    public static function validateUpdate( $data ) {
        $errors = [];
        $validated = [];

        // project_name - optional but if provided must be string, max 255
        if ( isset( $data['project_name'] ) ) {
            if ( empty( $data['project_name'] ) ) {
                $errors['project_name'] = 'Project name cannot be empty';
            } elseif ( strlen( $data['project_name'] ) > 255 ) {
                $errors['project_name'] = 'Project name must not exceed 255 characters';
            } else {
                $validated['project_name'] = sanitize_text_field( $data['project_name'] );
            }
        }

        // description - optional, string
        if ( isset( $data['description'] ) ) {
            if ( ! is_string( $data['description'] ) ) {
                $errors['description'] = 'Description must be a string';
            } else {
                $validated['description'] = wp_kses_post( $data['description'] );
            }
        }

        // start_date - optional, date
        if ( isset( $data['start_date'] ) ) {
            if ( ! self::isValidDate( $data['start_date'] ) ) {
                $errors['start_date'] = 'Start date must be a valid date (YYYY-MM-DD)';
            } else {
                $validated['start_date'] = $data['start_date'];
            }
        }

        // end_date - optional, date
        if ( isset( $data['end_date'] ) ) {
            if ( ! self::isValidDate( $data['end_date'] ) ) {
                $errors['end_date'] = 'End date must be a valid date (YYYY-MM-DD)';
            } else {
                $validated['end_date'] = $data['end_date'];
            }
        }

        // status - optional, must be valid enum
        if ( isset( $data['status'] ) ) {
            $valid_statuses = [ 'pending', 'in_progress', 'completed' ];
            if ( ! in_array( $data['status'], $valid_statuses ) ) {
                $errors['status'] = 'Status must be one of: pending, in_progress, completed';
            } else {
                $validated['status'] = $data['status'];
            }
        }

        return [
            'errors'    => $errors,
            'validated' => $validated,
            'passes'    => empty( $errors ),
        ];
    }

    /**
     * Check if date string is valid (YYYY-MM-DD format)
     * 
     * @param string $date
     * @return bool
     */
    private static function isValidDate( $date ) {
        $time = strtotime( $date );
        return $time && date( 'Y-m-d', $time ) === $date;
    }
}
?>