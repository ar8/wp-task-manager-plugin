<?php
// filepath: project-task-manager/src/Views/modals/project-modal.php

defined( 'ABSPATH' ) || exit;
?>

<!-- Project Create Modal -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectModalLabel">
                    <i class="fas fa-plus me-2"></i><?php esc_html_e( 'Create New Project', 'project-task-manager' ); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'project-task-manager' ); ?>"></button>
            </div>

            <form id="projectForm" class="needs-validation" novalidate>
                <?php wp_nonce_field( 'project_task_manager_nonce', 'project_nonce' ); ?>
                <input type="hidden" name="project_id" id="projectId" value="">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="projectName" class="form-label">
                            <?php esc_html_e( 'Name', 'project-task-manager' ); ?> <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="projectName" 
                            name="project_name"
                            placeholder="<?php esc_attr_e( 'Enter project name', 'project-task-manager' ); ?>"
                            required
                        >
                        <div class="invalid-feedback" id="projectNameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="projectDescription" class="form-label">
                            <?php esc_html_e( 'Description', 'project-task-manager' ); ?>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="projectDescription" 
                            name="description"
                            rows="3"
                            placeholder="<?php esc_attr_e( 'Enter project description', 'project-task-manager' ); ?>"
                        ></textarea>
                        <div class="invalid-feedback" id="projectDescriptionError"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="projectStartDate" class="form-label">
                                    <?php esc_html_e( 'Start Date', 'project-task-manager' ); ?>
                                </label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="projectStartDate" 
                                    name="start_date"
                                >
                                <div class="invalid-feedback" id="projectStartDateError"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="projectEndDate" class="form-label">
                                    <?php esc_html_e( 'End Date', 'project-task-manager' ); ?>
                                </label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="projectEndDate" 
                                    name="end_date"
                                >
                                <div class="invalid-feedback" id="projectEndDateError"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php esc_html_e( 'Cancel', 'project-task-manager' ); ?>
                    </button>
                    <button type="submit" class="btn btn-primary" id="projectSubmitBtn">
                        <i class="fas fa-save me-2"></i><?php esc_html_e( 'Create', 'project-task-manager' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Project Edit Modal -->
<div class="modal fade" id="projectModalEdit" tabindex="-1" aria-labelledby="projectModalLabelEdit" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectModalLabelEdit">
                    <i class="fas fa-edit me-2"></i><?php esc_html_e( 'Edit Project', 'project-task-manager' ); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'project-task-manager' ); ?>"></button>
            </div>

            <form id="projectFormEdit" class="needs-validation" novalidate>
                <?php wp_nonce_field( 'project_task_manager_nonce', 'project_nonce_edit' ); ?>
                <input type="hidden" name="project_id" id="projectIdEdit" value="">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="projectNameEdit" class="form-label">
                            <?php esc_html_e( 'Name', 'project-task-manager' ); ?> <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="projectNameEdit" 
                            name="project_name"
                            placeholder="<?php esc_attr_e( 'Enter project name', 'project-task-manager' ); ?>"
                            required
                        >
                        <div class="invalid-feedback" id="projectNameEditError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="projectDescriptionEdit" class="form-label">
                            <?php esc_html_e( 'Description', 'project-task-manager' ); ?>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="projectDescriptionEdit" 
                            name="description"
                            rows="3"
                            placeholder="<?php esc_attr_e( 'Enter project description', 'project-task-manager' ); ?>"
                        ></textarea>
                        <div class="invalid-feedback" id="projectDescriptionEditError"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="projectStartDateEdit" class="form-label">
                                    <?php esc_html_e( 'Start Date', 'project-task-manager' ); ?>
                                </label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="projectStartDateEdit" 
                                    name="start_date"
                                >
                                <div class="invalid-feedback" id="projectStartDateEditError"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="projectEndDateEdit" class="form-label">
                                    <?php esc_html_e( 'End Date', 'project-task-manager' ); ?>
                                </label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="projectEndDateEdit" 
                                    name="end_date"
                                >
                                <div class="invalid-feedback" id="projectEndDateEditError"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php esc_html_e( 'Cancel', 'project-task-manager' ); ?>
                    </button>
                    <button type="submit" class="btn btn-primary" id="projectEditSubmitBtn">
                        <i class="fas fa-save me-2"></i><?php esc_html_e( 'Save', 'project-task-manager' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>