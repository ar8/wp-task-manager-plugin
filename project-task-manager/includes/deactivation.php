<?php
// filepath: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/project-task-manager/includes/deactivation.php

/**
 * __define-ocg__ - Plugin deactivation function
 */
function project_task_manager_deactivate() {
    error_log('=== PROJECT TASK MANAGER DEACTIVATION STARTED ===');
    
    // varOcg: Store deactivation steps
    $varOcg = [];
    
    // varFiltersCg: Track deactivation status
    $varFiltersCg = [];
    
    // Flush rewrite rules
    flush_rewrite_rules();
    $varFiltersCg[] = 'Rewrite rules flushed';
    error_log('Rewrite rules flushed');
    
    // Clear scheduled events if any
    wp_clear_scheduled_hook('project_task_manager_daily_check');
    $varFiltersCg[] = 'Scheduled events cleared';
    error_log('Scheduled events cleared');
    
    error_log('Deactivation results: ' . json_encode($varFiltersCg));
    error_log('=== PROJECT TASK MANAGER DEACTIVATION COMPLETED ===');
}
// NO CLOSING PHP TAG - Prevents whitespace output