<?php
// filepath: project-task-manager/src/Views/modals/task-modal.php

defined( 'ABSPATH' ) || exit;
?>

<!-- Task Create Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">
                    <i class="fas fa-plus me-2"></i><?php esc_html_e( 'Create New Task', 'project-task-manager' ); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'project-task-manager' ); ?>"></button>
            </div>

            <form id="taskForm" class="needs-validation" novalidate>
                <?php wp_nonce_field( 'project_task_manager_nonce', 'task_nonce' ); ?>
                <input type="hidden" name="task_id" id="taskId" value="">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="taskName" class="form-label">
                            <?php esc_html_e( 'Task Name', 'project-task-manager' ); ?> <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="taskName" 
                            name="task_name"
                            placeholder="<?php esc_attr_e( 'Enter task name', 'project-task-manager' ); ?>"
                            required
                        >
                        <div class="invalid-feedback" id="taskNameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">
                            <?php esc_html_e( 'Description', 'project-task-manager' ); ?>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="taskDescription" 
                            name="description"
                            rows="3"
                            placeholder="<?php esc_attr_e( 'Enter task description', 'project-task-manager' ); ?>"
                        ></textarea>
                        <div class="invalid-feedback" id="taskDescriptionError"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskPriority" class="form-label">
                                    <?php esc_html_e( 'Priority', 'project-task-manager' ); ?>
                                </label>
                                <select class="form-select" id="taskPriority" name="priority">
                                    <option value="low"><?php esc_html_e( 'Low', 'project-task-manager' ); ?></option>
                                    <option value="medium" selected><?php esc_html_e( 'Medium', 'project-task-manager' ); ?></option>
                                    <option value="high"><?php esc_html_e( 'High', 'project-task-manager' ); ?></option>
                                </select>
                                <div class="invalid-feedback" id="taskPriorityError"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskDueDate" class="form-label">
                                    <?php esc_html_e( 'Due Date', 'project-task-manager' ); ?>
                                </label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="taskDueDate" 
                                    name="due_date"
                                >
                                <div class="invalid-feedback" id="taskDueDateError"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="taskProject" class="form-label">
                            <?php esc_html_e( 'Project', 'project-task-manager' ); ?>
                        </label>
                        <select class="form-select" id="taskProject" name="project_id">
                            <option value=""><?php esc_html_e( 'Select Project (Optional)', 'project-task-manager' ); ?></option>
                        </select>
                        <div class="invalid-feedback" id="taskProjectError"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php esc_html_e( 'Cancel', 'project-task-manager' ); ?>
                    </button>
                    <button type="submit" class="btn btn-primary" id="taskSubmitBtn">
                        <i class="fas fa-save me-2"></i><?php esc_html_e( 'Create', 'project-task-manager' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Edit Modal -->
<div class="modal fade" id="taskModalEdit" tabindex="-1" aria-labelledby="taskModalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalEditLabel">
                    <i class="fas fa-edit me-2"></i><?php esc_html_e( 'Edit Task', 'project-task-manager' ); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'project-task-manager' ); ?>"></button>
            </div>

            <form id="taskFormEdit" class="needs-validation" novalidate>
                <?php wp_nonce_field( 'project_task_manager_nonce', 'task_nonce_edit' ); ?>
                <input type="hidden" name="task_id" id="taskIdEdit" value="">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="taskNameEdit" class="form-label">
                            <?php esc_html_e( 'Task Name', 'project-task-manager' ); ?> <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="taskNameEdit" 
                            name="task_name"
                            placeholder="<?php esc_attr_e( 'Enter task name', 'project-task-manager' ); ?>"
                            required
                        >
                        <div class="invalid-feedback" id="taskNameEditError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="taskDescriptionEdit" class="form-label">
                            <?php esc_html_e( 'Description', 'project-task-manager' ); ?>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="taskDescriptionEdit" 
                            name="description"
                            rows="3"
                            placeholder="<?php esc_attr_e( 'Enter task description', 'project-task-manager' ); ?>"
                        ></textarea>
                        <div class="invalid-feedback" id="taskDescriptionEditError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="taskProjectEdit" class="form-label">
                            <?php esc_html_e( 'Project', 'project-task-manager' ); ?>
                        </label>
                        <select class="form-select" id="taskProjectEdit" name="project_id">
                            <option value=""><?php esc_html_e( 'Select Project (Optional)', 'project-task-manager' ); ?></option>
                        </select>
                        <div class="invalid-feedback" id="taskProjectEditError"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskPriorityEdit" class="form-label">
                                    <?php esc_html_e( 'Priority', 'project-task-manager' ); ?>
                                </label>
                                <select class="form-select" id="taskPriorityEdit" name="priority">
                                    <option value="low"><?php esc_html_e( 'Low', 'project-task-manager' ); ?></option>
                                    <option value="medium" selected><?php esc_html_e( 'Medium', 'project-task-manager' ); ?></option>
                                    <option value="high"><?php esc_html_e( 'High', 'project-task-manager' ); ?></option>
                                </select>
                                <div class="invalid-feedback" id="taskPriorityEditError"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskDueDateEdit" class="form-label">
                                    <?php esc_html_e( 'Due Date', 'project-task-manager' ); ?>
                                </label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="taskDueDateEdit" 
                                    name="due_date"
                                    pattern="\d{4}-\d{2}-\d{2}"
                                >
                                <div class="invalid-feedback" id="taskDueDateEditError"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="taskStatusEdit" class="form-label">
                            <?php esc_html_e( 'Status', 'project-task-manager' ); ?>
                        </label>
                        <select class="form-select" id="taskStatusEdit" name="status">
                            <option value="pending"><?php esc_html_e( 'Pending', 'project-task-manager' ); ?></option>
                            <option value="in_progress" selected><?php esc_html_e( 'In Progress', 'project-task-manager' ); ?></option>
                            <option value="completed"><?php esc_html_e( 'Completed', 'project-task-manager' ); ?></option>
                        </select>
                        <div class="invalid-feedback" id="taskStatusEditError"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php esc_html_e( 'Cancel', 'project-task-manager' ); ?>
                    </button>
                    <button type="submit" class="btn btn-primary" id="taskEditSubmitBtn">
                        <i class="fas fa-save me-2"></i><?php esc_html_e( 'Save', 'project-task-manager' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>