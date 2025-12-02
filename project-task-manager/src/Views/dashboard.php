<?php
// filepath: project-task-manager/src/Views/dashboard.php

defined( 'ABSPATH' ) || exit;
?>

<!DOCTYPE html>
<html lang="<?php language_attributes(); ?>">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_attr_e( 'Project Task Management - Drag & Drop', 'project-task-manager' ); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo esc_url( plugins_url( 'assets/css/dashboard.css', dirname( __FILE__ ) ) ); ?>">

    <!-- JS --> 
    <script src="<?php echo esc_url( plugins_url( 'assets/js/dashboard.js', dirname( __FILE__ ) ) ); ?>" defer></script>
    
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h3 mb-2">
                            <i class="fas fa-tasks me-3 text-primary"></i><?php esc_html_e( 'Project Task Management System', 'project-task-manager' ); ?>
                        </h1>
                        <p class="text-muted mb-0"><?php esc_html_e( 'Drag and drop tasks to manage their status', 'project-task-manager' ); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Selection & Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label for="projectSelect" class="form-label fw-bold">
                                    <i class="fas fa-project-diagram me-2"></i><?php esc_html_e( 'Filter by Project:', 'project-task-manager' ); ?>
                                </label>
                                <div class="input-group">
                                    <select id="projectSelect" class="form-select">
                                        <option value=""><?php esc_html_e( 'All Projects', 'project-task-manager' ); ?></option>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" onclick="editProjectModal()" title="<?php esc_attr_e( 'Edit Project', 'project-task-manager' ); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteProject()" title="<?php esc_attr_e( 'Delete Project', 'project-task-manager' ); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal">
                                    <i class="fas fa-plus me-2"></i><?php esc_html_e( 'New Project', 'project-task-manager' ); ?>
                                </button>
                                <button type="button" class="btn btn-primary" onclick="openTaskModal()">
                                    <i class="fas fa-plus me-2"></i><?php esc_html_e( 'New Task', 'project-task-manager' ); ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary ms-2" onclick="refreshTasks()">
                                    <i class="fas fa-sync-alt me-2"></i><?php esc_html_e( 'Refresh', 'project-task-manager' ); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center stats-card shadow-sm">
                    <div class="card-body">
                        <div class="h2 text-primary mb-1" id="totalTasks">0</div>
                        <div class="text-muted small"><?php esc_html_e( 'Total Tasks', 'project-task-manager' ); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center stats-card shadow-sm">
                    <div class="card-body">
                        <div class="h2 text-success mb-1" id="completedTasks">0</div>
                        <div class="text-muted small"><?php esc_html_e( 'Completed', 'project-task-manager' ); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center stats-card shadow-sm">
                    <div class="card-body">
                        <div class="h2 text-warning mb-1" id="pendingTasks">0</div>
                        <div class="text-muted small"><?php esc_html_e( 'Pending', 'project-task-manager' ); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center stats-card shadow-sm">
                    <div class="card-body">
                        <div class="h2 text-danger mb-1" id="overdueTasks">0</div>
                        <div class="text-muted small"><?php esc_html_e( 'Overdue', 'project-task-manager' ); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Columns -->
        <div class="row">
            <!-- Pending Tasks Column -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i><?php esc_html_e( 'Pending Tasks', 'project-task-manager' ); ?>
                            <span id="pendingCount" class="badge bg-dark float-end">0</span>
                        </h5>
                    </div>
                    <div class="card-body p-2">
                        <div id="pendingTasksList" class="task-column" data-status="pending">
                            <!-- Pending tasks will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Progress Tasks Column -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-play me-2"></i><?php esc_html_e( 'In Progress', 'project-task-manager' ); ?>
                            <span id="progressCount" class="badge bg-dark float-end">0</span>
                        </h5>
                    </div>
                    <div class="card-body p-2">
                        <div id="inProgressTasksList" class="task-column" data-status="in_progress">
                            <!-- In progress tasks will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Tasks Column -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-check me-2"></i><?php esc_html_e( 'Completed', 'project-task-manager' ); ?>
                            <span id="completedCount" class="badge bg-dark float-end">0</span>
                        </h5>
                    </div>
                    <div class="card-body p-2">
                        <div id="completedTasksList" class="task-column" data-status="completed">
                            <!-- Completed tasks will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.5); z-index: 1050;">
        <div class="card shadow p-3 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php esc_html_e( 'Loading...', 'project-task-manager' ); ?></span>
            </div>
            <div class="mt-2 text-muted"><?php esc_html_e( 'Loading tasks...', 'project-task-manager' ); ?></div>
        </div>
    </div>

    <!-- Modals -->
    <?php include PROJECT_TASK_MANAGER_PATH . 'src/Views/modals/project-modal.php'; ?>
    <?php include PROJECT_TASK_MANAGER_PATH . 'src/Views/modals/task-modal.php'; ?>
    
    <!-- Toast Notifications Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

    <!-- Sortable.js for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript Variables -->
    <script type="text/javascript">
        var projectTaskManagerNonce = '<?php echo wp_create_nonce( 'project_task_manager_nonce' ); ?>';
        var projectTaskManagerAPI = '<?php echo rest_url( 'project-task-manager/v1' ); ?>';
        var projectTaskManagerI18n = {
            allProjects: '<?php esc_js( __( 'All Projects', 'project-task-manager' ) ); ?>',
            loadingTasks: '<?php esc_js( __( 'Loading tasks...', 'project-task-manager' ) ); ?>',
            editProject: '<?php esc_js( __( 'Edit Project', 'project-task-manager' ) ); ?>',
            deleteConfirm: '<?php esc_js( __( 'Are you sure?', 'project-task-manager' ) ); ?>',
            taskDeleted: '<?php esc_js( __( 'Task deleted successfully', 'project-task-manager' ) ); ?>',
            projectDeleted: '<?php esc_js( __( 'Project deleted successfully', 'project-task-manager' ) ); ?>',
        };
    </script>
</body>
</html>