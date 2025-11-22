/**
 * Task Manager - Main Application Script
 * Handles all task and project management operations
 * No jQuery dependency - Pure vanilla JavaScript
 */

// ============ GLOBAL VARIABLES ============

let allTasks = [];
let allProjects = [];
let currentProjectId = '';
const priorityLevels = ['low', 'medium', 'high'];

// API configuration from wp_localize_script
const API_BASE = typeof taskManagerAPI !== 'undefined' ? taskManagerAPI : '/wp-json/task-manager/v1';
const NONCE = typeof taskManagerNonce !== 'undefined' ? taskManagerNonce : '';
const i18n = typeof taskManagerI18n !== 'undefined' ? taskManagerI18n : {};

// ============ INITIALIZATION ============

/**
 * Initialize app when DOM is ready
 */
document.addEventListener( 'DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the entire application
 */
function initializeApp() {
    console.log( 'Initializing Task Manager App...' );
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize sortable for drag & drop
    initializeSortable();
    
    // Load initial data
    loadProjects();
    loadTasks();
    
    console.log( 'Task Manager App initialized!' );
}

// ============ EVENT LISTENERS ============

/**
 * Setup all event listeners for modals and forms
 */
function setupEventListeners() {
    
    // Project selection filter
    const projectSelect = document.getElementById( 'projectSelect' );
    if ( projectSelect ) {
        projectSelect.addEventListener( 'change', function() {
            currentProjectId = this.value;
            filterAndDisplayTasks();
        });
    }

    // Project Form - Create
    const projectForm = document.getElementById( 'projectForm' );
    if ( projectForm ) {
        projectForm.addEventListener( 'submit', function( e ) {
            e.preventDefault();
            createProject();
        });
    }

    // Project Form - Edit
    const projectFormEdit = document.getElementById( 'projectFormEdit' );
    if ( projectFormEdit ) {
        projectFormEdit.addEventListener( 'submit', function( e ) {
            e.preventDefault();
            editProject();
        });
    }

    // Task Form - Create
    const taskForm = document.getElementById( 'taskForm' );
    if ( taskForm ) {
        taskForm.addEventListener( 'submit', function( e ) {
            e.preventDefault();
            createTask();
        });
    }

    // Task Form - Edit
    const taskFormEdit = document.getElementById( 'taskFormEdit' );
    if ( taskFormEdit ) {
        taskFormEdit.addEventListener( 'submit', function( e ) {
            e.preventDefault();
            editTask();
        });
    }

    // Modal reset listeners
    const taskModal = document.getElementById( 'taskModal' );
    if ( taskModal ) {
        taskModal.addEventListener( 'hidden.bs.modal', function() {
            resetTaskForm();
        });
    }

    const taskModalEdit = document.getElementById( 'taskModalEdit' );
    if ( taskModalEdit ) {
        taskModalEdit.addEventListener( 'hidden.bs.modal', function() {
            resetTaskFormEdit();
        });
    }

    const projectModal = document.getElementById( 'projectModal' );
    if ( projectModal ) {
        projectModal.addEventListener( 'hidden.bs.modal', function() {
            resetProjectForm();
        });
    }

    const projectModalEdit = document.getElementById( 'projectModalEdit' );
    if ( projectModalEdit ) {
        projectModalEdit.addEventListener( 'hidden.bs.modal', function() {
            resetProjectFormEdit();
        });
    }
}

// ============ DRAG & DROP INITIALIZATION ============

/**
 * Initialize Sortable.js for drag and drop functionality
 */
function initializeSortable() {
    const containers = [
        'pendingTasksList',
        'inProgressTasksList',
        'completedTasksList'
    ];

    containers.forEach( ( containerId ) => {
        const container = document.getElementById( containerId );
        if ( ! container ) return;

        new Sortable( container, {
            group: 'tasks',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function( evt ) {
                handleTaskMove( evt );
            }
        });
    });
}

// ============ MODAL OPENING FUNCTIONS ============

/**
 * Open create project modal
 */
function openCreateProjectModal() {
    resetProjectForm();
    const modal = new bootstrap.Modal( document.getElementById( 'projectModal' ) );
    modal.show();
}

/**
 * Open edit project modal
 */
async function openEditProjectModal() {
    const projectId = document.getElementById( 'projectSelect' ).value;

    if ( ! projectId ) {
        showNotification( 'Please select a project to edit', 'warning' );
        return;
    }

    try {
        showSpinner( true );

        const response = await fetch( `${API_BASE}/projects/${projectId}`, {
            headers: {
                'X-WP-Nonce': NONCE
            }
        });

        if ( ! response.ok ) {
            throw new Error( 'Failed to load project' );
        }

        const data = await response.json();
        const project = data.data;

        // Populate form
        document.getElementById( 'projectIdEdit' ).value = projectId;
        document.getElementById( 'projectNameEdit' ).value = project.project_name || '';
        document.getElementById( 'projectDescriptionEdit' ).value = project.description || '';
        document.getElementById( 'projectStartDateEdit' ).value = formatDate( project.start_date ) || '';
        document.getElementById( 'projectEndDateEdit' ).value = formatDate( project.end_date ) || '';

        const modal = new bootstrap.Modal( document.getElementById( 'projectModalEdit' ) );
        modal.show();
    } catch ( error ) {
        console.error( 'Error loading project:', error );
        showNotification( 'Error loading project details', 'danger' );
    } finally {
        showSpinner( false );
    }
}

/**
 * Open create task modal
 */
function openCreateTaskModal() {
    resetTaskForm();
    
    // If a project is selected, pre-fill it
    const projectId = document.getElementById( 'projectSelect' ).value;
    if ( projectId ) {
        document.getElementById( 'taskProject' ).value = projectId;
    }

    const modal = new bootstrap.Modal( document.getElementById( 'taskModal' ) );
    modal.show();
}

/**
 * Open edit task modal
 */
function openEditTaskModal( taskId ) {
    const task = allTasks.find( t => t.id == taskId );
    if ( ! task ) {
        console.error( 'Task not found:', taskId );
        return;
    }

    // Populate form
    document.getElementById( 'taskIdEdit' ).value = taskId;
    document.getElementById( 'taskNameEdit' ).value = task.task_name || '';
    document.getElementById( 'taskDescriptionEdit' ).value = task.description || '';
    document.getElementById( 'taskDueDateEdit' ).value = formatDate( task.due_date ) || '';
    document.getElementById( 'taskProjectEdit' ).value = task.project_id || '';
    document.getElementById( 'taskPriorityEdit' ).value = task.priority || 'medium';
    document.getElementById( 'taskStatusEdit' ).value = task.status || 'pending';

    const modal = new bootstrap.Modal( document.getElementById( 'taskModalEdit' ) );
    modal.show();
}

// ============ PROJECT OPERATIONS ============

/**
 * Load projects from API
 */
async function loadProjects() {
    try {
        const response = await fetch( `${API_BASE}/projects`, {
            headers: {
                'X-WP-Nonce': NONCE
            }
        });

        if ( ! response.ok ) {
            throw new Error( 'Failed to load projects' );
        }

        const data = await response.json();
        allProjects = data.data || [];

        // Populate project dropdowns
        populateProjectDropdowns();
    } catch ( error ) {
        console.error( 'Error loading projects:', error );
        showNotification( i18n.errorLoadingProjects || 'Error loading projects', 'danger' );
    }
}

/**
 * Populate project dropdown selects
 */
function populateProjectDropdowns() {
    const projectSelect = document.getElementById( 'projectSelect' );
    const taskProjectSelect = document.getElementById( 'taskProject' );
    const taskProjectSelectEdit = document.getElementById( 'taskProjectEdit' );

    // Reset dropdowns
    if ( projectSelect ) {
        projectSelect.innerHTML = `<option value="">${i18n.allProjects || 'All Projects'}</option>`;
    }
    if ( taskProjectSelect ) {
        taskProjectSelect.innerHTML = `<option value="">${i18n.selectProjectOptional || 'Select Project (Optional)'}</option>`;
    }
    if ( taskProjectSelectEdit ) {
        taskProjectSelectEdit.innerHTML = `<option value="">${i18n.selectProjectOptional || 'Select Project (Optional)'}</option>`;
    }

    // Populate options
    allProjects.forEach( ( project ) => {
        const projectName = project.project_name || project.name;
        const option = document.createElement( 'option' );
        option.value = project.id;
        option.textContent = projectName;

        if ( projectSelect ) {
            projectSelect.appendChild( option.cloneNode( true ) );
        }
        if ( taskProjectSelect ) {
            taskProjectSelect.appendChild( option.cloneNode( true ) );
        }
        if ( taskProjectSelectEdit ) {
            taskProjectSelectEdit.appendChild( option.cloneNode( true ) );
        }
    });
}

/**
 * Create new project
 */
async function createProject() {
    const form = document.getElementById( 'projectForm' );
    if ( ! form.checkValidity() ) {
        form.classList.add( 'was-validated' );
        return;
    }

    const projectData = {
        project_name: document.getElementById( 'projectName' ).value,
        description: document.getElementById( 'projectDescription' ).value,
        start_date: document.getElementById( 'projectStartDate' ).value,
        end_date: document.getElementById( 'projectEndDate' ).value,
    };

    try {
        showSpinner( true );

        const response = await fetch( `${API_BASE}/projects`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE
            },
            body: JSON.stringify( projectData )
        });

        if ( ! response.ok ) {
            const error = await response.json();
            throw new Error( error.message || 'Failed to create project' );
        }

        showNotification( i18n.projectCreated || 'Project created successfully', 'success' );

        // Close modal
        const modal = bootstrap.Modal.getInstance( document.getElementById( 'projectModal' ) );
        if ( modal ) {
            modal.hide();
        }

        // Refresh data
        loadProjects();
        loadTasks();
    } catch ( error ) {
        console.error( 'Error creating project:', error );
        showNotification( error.message || i18n.errorCreatingProject || 'Error creating project', 'danger' );
    } finally {
        showSpinner( false );
    }
}

/**
 * Edit existing project
 */
async function editProject() {
    const form = document.getElementById( 'projectFormEdit' );
    if ( ! form.checkValidity() ) {
        form.classList.add( 'was-validated' );
        return;
    }

    const projectId = document.getElementById( 'projectIdEdit' ).value;
    const projectData = {
        project_name: document.getElementById( 'projectNameEdit' ).value,
        description: document.getElementById( 'projectDescriptionEdit' ).value,
        start_date: document.getElementById( 'projectStartDateEdit' ).value,
        end_date: document.getElementById( 'projectEndDateEdit' ).value,
    };

    try {
        showSpinner( true );

        const response = await fetch( `${API_BASE}/projects/${projectId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE
            },
            body: JSON.stringify( projectData )
        });

        if ( ! response.ok ) {
            const error = await response.json();
            throw new Error( error.message || 'Failed to update project' );
        }

        showNotification( i18n.projectUpdated || 'Project updated successfully', 'success' );

        // Close modal
        const modal = bootstrap.Modal.getInstance( document.getElementById( 'projectModalEdit' ) );
        if ( modal ) {
            modal.hide();
        }

        // Refresh data
        loadProjects();
        loadTasks();
    } catch ( error ) {
        console.error( 'Error updating project:', error );
        showNotification( error.message || i18n.errorUpdatingProject || 'Error updating project', 'danger' );
    } finally {
        showSpinner( false );
    }
}

/**
 * Delete project
 */
async function deleteProject() {
    const projectId = document.getElementById( 'projectSelect' ).value;

    if ( ! projectId ) {
        showNotification( 'Please select a project to delete', 'warning' );
        return;
    }

    const project = allProjects.find( p => p.id == projectId );
    if ( ! project ) return;

    const projectName = project.project_name || project.name;
    const confirmDelete = confirm(
        `Are you sure you want to delete the project "${projectName}"? This will delete all tasks associated with this project.`
    );

    if ( ! confirmDelete ) {
        return;
    }

    try {
        showSpinner( true );

        const response = await fetch( `${API_BASE}/projects/${projectId}`, {
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': NONCE
            }
        });

        if ( ! response.ok ) {
            const error = await response.json();
            throw new Error( error.message || 'Failed to delete project' );
        }

        allProjects = allProjects.filter( p => p.id != projectId );
        currentProjectId = '';

        showNotification( i18n.projectDeleted || 'Project deleted successfully', 'success' );

        loadProjects();
        loadTasks();
    } catch ( error ) {
        console.error( 'Error deleting project:', error );
        showNotification( error.message || 'Error deleting project', 'danger' );
    } finally {
        showSpinner( false );
    }
}

// ============ TASK OPERATIONS ============

/**
 * Load tasks from API
 */
async function loadTasks() {
    showSpinner( true );
    try {
        let url = `${API_BASE}/tasks`;
        if ( currentProjectId ) {
            url += `?project_id=${currentProjectId}`;
        }

        const response = await fetch( url, {
            headers: {
                'X-WP-Nonce': NONCE
            }
        });

        if ( ! response.ok ) {
            throw new Error( 'Failed to load tasks' );
        }

        const data = await response.json();
        allTasks = ( data.data || [] ).sort( ( a, b ) => {
            const priorityOrder = { 'high': 1, 'medium': 2, 'low': 3 };
            return priorityOrder[a.priority] - priorityOrder[b.priority];
        });

        filterAndDisplayTasks();
        updateStats();
    } catch ( error ) {
        console.error( 'Error loading tasks:', error );
        showNotification( i18n.errorLoadingTasks || 'Error loading tasks', 'danger' );
    } finally {
        showSpinner( false );
    }
}

/**
 * Filter and display tasks
 */
function filterAndDisplayTasks() {
    let filteredTasks = allTasks;

    if ( currentProjectId ) {
        filteredTasks = allTasks.filter( task => task.project_id == currentProjectId );
    }

    displayTasks( filteredTasks );
    updateStats();
}

/**
 * Display tasks in columns
 */
function displayTasks( tasks ) {
    const pendingContainer = document.getElementById( 'pendingTasksList' );
    const progressContainer = document.getElementById( 'inProgressTasksList' );
    const completedContainer = document.getElementById( 'completedTasksList' );

    if ( ! pendingContainer || ! progressContainer || ! completedContainer ) return;

    // Clear containers
    pendingContainer.innerHTML = '';
    progressContainer.innerHTML = '';
    completedContainer.innerHTML = '';

    // Separate tasks by status
    const pendingTasks = tasks.filter( task => task.status === 'pending' );
    const progressTasks = tasks.filter( task => task.status === 'in_progress' );
    const completedTasks = tasks.filter( task => task.status === 'completed' );

    // Render tasks
    renderTasksInContainer( pendingTasks, pendingContainer );
    renderTasksInContainer( progressTasks, progressContainer );
    renderTasksInContainer( completedTasks, completedContainer );

    // Update counts
    const pendingCount = document.getElementById( 'pendingCount' );
    const progressCount = document.getElementById( 'progressCount' );
    const completedCount = document.getElementById( 'completedCount' );

    if ( pendingCount ) pendingCount.textContent = pendingTasks.length;
    if ( progressCount ) progressCount.textContent = progressTasks.length;
    if ( completedCount ) completedCount.textContent = completedTasks.length;
}

/**
 * Render tasks in a container
 */
function renderTasksInContainer( tasks, container ) {
    if ( tasks.length === 0 ) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                No tasks
            </div>
        `;
        return;
    }

    tasks.forEach( ( task ) => {
        const taskElement = createTaskElement( task );
        container.appendChild( taskElement );
    });
}

/**
 * Create task HTML element
 */
function createTaskElement( task ) {
    const div = document.createElement( 'div' );
    div.className = `task-card priority-${task.priority} ${task.status === 'completed' ? 'task-completed' : ''}`;
    div.dataset.taskId = task.id;
    div.dataset.status = task.status;

    const priorityClass = {
        'high': 'priority-high',
        'medium': 'priority-medium',
        'low': 'priority-low'
    };

    const dueDateClass = isOverdue( task.due_date ) && task.status !== 'completed' ? 'overdue' : '';
    const dueDateText = task.due_date ? formatDate( task.due_date ) : 'N/A';

    div.innerHTML = `
        <div class="task-title">${escapeHtml( task.task_name )}</div>
        ${task.description ? `<div class="task-description">${escapeHtml( task.description )}</div>` : ''}
        <div class="task-meta">
            <span class="task-priority ${priorityClass[task.priority]}">${task.priority.toUpperCase()}</span>
            <span class="task-due-date ${dueDateClass}"><i class="fas fa-calendar-alt"></i> ${dueDateText}</span>
        </div>
        <div class="task-actions">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditTaskModal(${task.id})">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTask(${task.id})">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    `;

    return div;
}

/**
 * Handle task drag and drop
 */
function handleTaskMove( evt ) {
    const taskId = evt.item.dataset.taskId;
    const newContainer = evt.to.id;
    const task = allTasks.find( t => t.id == taskId );

    if ( ! task ) return;

    // Determine new status
    let newStatus = task.status;
    switch ( newContainer ) {
        case 'completedTasksList':
            newStatus = 'completed';
            break;
        case 'pendingTasksList':
            newStatus = 'pending';
            break;
        case 'inProgressTasksList':
            newStatus = 'in_progress';
            break;
    }

    if ( newStatus !== task.status ) {
        updateTaskStatus( taskId, newStatus );
    }
}

/**
 * Create new task
 */
async function createTask() {
    const form = document.getElementById( 'taskForm' );
    if ( ! form.checkValidity() ) {
        form.classList.add( 'was-validated' );
        return;
    }

    const taskData = {
        task_name: document.getElementById( 'taskName' ).value,
        description: document.getElementById( 'taskDescription' ).value,
        priority: document.getElementById( 'taskPriority' ).value,
        due_date: document.getElementById( 'taskDueDate' ).value,
        project_id: document.getElementById( 'taskProject' ).value || null,
        status: 'pending'
    };

    try {
        showSpinner( true );

        const response = await fetch( `${API_BASE}/tasks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE
            },
            body: JSON.stringify( taskData )
        });

        if ( ! response.ok ) {
            const error = await response.json();
            throw new Error( error.message || 'Failed to create task' );
        }

        showNotification( i18n.taskCreated || 'Task created successfully', 'success' );

        // Close modal
        const modal = bootstrap.Modal.getInstance( document.getElementById( 'taskModal' ) );
        if ( modal ) {
            modal.hide();
        }

        // Refresh data
        loadTasks();
    } catch ( error ) {
        console.error( 'Error creating task:', error );
        showNotification( error.message || i18n.errorCreatingTask || 'Error creating task', 'danger' );
    } finally {
        showSpinner( false );
    }
}

/**
 * Edit existing task
 */
async function editTask() {
    const form = document.getElementById( 'taskFormEdit' );
    if ( ! form.checkValidity() ) {
        form.classList.add( 'was-validated' );
        return;
    }

    const taskId = document.getElementById( 'taskIdEdit' ).value;
    const taskData = {
        task_name: document.getElementById( 'taskNameEdit' ).value,
        description: document.getElementById( 'taskDescriptionEdit' ).value,
        priority: document.getElementById( 'taskPriorityEdit' ).value,
        due_date: document.getElementById( 'taskDueDateEdit' ).value,
        project_id: document.getElementById( 'taskProjectEdit' ).value || null,
        status: document.getElementById( 'taskStatusEdit' ).value || 'pending'
    };

    try {
        showSpinner( true );

        const response = await fetch( `${API_BASE}/tasks/${taskId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE
            },
            body: JSON.stringify( taskData )
        });

        if ( ! response.ok ) {
            const error = await response.json();
            throw new Error( error.message || 'Failed to update task' );
        }

        showNotification( i18n.taskUpdated || 'Task updated successfully', 'success' );

        // Close modal
        const modal = bootstrap.Modal.getInstance( document.getElementById( 'taskModalEdit' ) );
        if ( modal ) {
            modal.hide();
        }

        // Refresh data
        loadTasks();
    } catch ( error ) {
        console.error( 'Error updating task:', error );
        showNotification( error.message || i18n.errorUpdatingTask || 'Error updating task', 'danger' );
    } finally {
        showSpinner( false );
    }
}

/**
 * Delete task
 */
async function deleteTask( taskId ) {
    const confirmDelete = confirm( i18n.deleteConfirmTask || 'Are you sure you want to delete this task?' );
    if ( ! confirmDelete ) {
        return;
    }

    try {
        showSpinner( true );

        const response = await fetch( `${API_BASE}/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': NONCE
            }
        });

        if ( ! response.ok ) {
            const error = await response.json();
            throw new Error( error.message || 'Failed to delete task' );
        }

        allTasks = allTasks.filter( task => task.id != taskId );
        filterAndDisplayTasks();
        updateStats();

        showNotification( i18n.taskDeleted || 'Task deleted successfully', 'success' );
    } catch ( error ) {
        console.error( 'Error deleting task:', error );
        showNotification( error.message || 'Error deleting task', 'danger' );
    } finally {
        showSpinner( false );
    }
}

/**
 * Update task status (via drag & drop)
 */
async function updateTaskStatus( taskId, status ) {
    try {
        const response = await fetch( `${API_BASE}/tasks/${taskId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE
            },
            body: JSON.stringify( { status: status } )
        });

        if ( ! response.ok ) {
            throw new Error( 'Failed to update task status' );
        }

        // Update local data
        const taskIndex = allTasks.findIndex( task => task.id == taskId );
        if ( taskIndex !== -1 ) {
            allTasks[taskIndex].status = status;
            updateStats();
        }

        showNotification( 'Task status updated', 'success' );
    } catch ( error ) {
        console.error( 'Error updating task:', error );
        showNotification( 'Error updating task status', 'danger' );
        loadTasks(); // Reload to revert changes
    }
}

/**
 * Refresh all tasks manually
 */
function refreshTasks() {
    loadTasks();
    showNotification( 'Tasks refreshed', 'success' );
}

// ============ STATISTICS ============

/**
 * Update statistics display
 */
function updateStats() {
    const filteredTasks = currentProjectId ?
        allTasks.filter( task => task.project_id == currentProjectId ) :
        allTasks;

    const totalTasks = filteredTasks.length;
    const completed = filteredTasks.filter( task => task.status === 'completed' ).length;
    const pending = filteredTasks.filter( task => task.status === 'pending' ).length;
    const overdue = filteredTasks.filter( task => task.status !== 'completed' && isOverdue( task.due_date ) ).length;
    const inProgress = filteredTasks.filter( task => task.status === 'in_progress' ).length;

    // Update stats cards
    const totalElement = document.getElementById( 'totalTasks' );
    const completedElement = document.getElementById( 'completedTasks' );
    const pendingElement = document.getElementById( 'pendingTasks' );
    const overdueElement = document.getElementById( 'overdueTasks' );

    if ( totalElement ) totalElement.textContent = totalTasks;
    if ( completedElement ) completedElement.textContent = completed;
    if ( pendingElement ) pendingElement.textContent = pending;
    if ( overdueElement ) overdueElement.textContent = overdue;
}

// ============ FORM RESET FUNCTIONS ============

/**
 * Reset task create form
 */
function resetTaskForm() {
    const form = document.getElementById( 'taskForm' );
    if ( form ) {
        form.reset();
        form.classList.remove( 'was-validated' );
    }
    document.getElementById( 'taskId' ).value = '';
}

/**
 * Reset task edit form
 */
function resetTaskFormEdit() {
    const form = document.getElementById( 'taskFormEdit' );
    if ( form ) {
        form.reset();
        form.classList.remove( 'was-validated' );
    }
    document.getElementById( 'taskIdEdit' ).value = '';
}

/**
 * Reset project create form
 */
function resetProjectForm() {
    const form = document.getElementById( 'projectForm' );
    if ( form ) {
        form.reset();
        form.classList.remove( 'was-validated' );
    }
}

/**
 * Reset project edit form
 */
function resetProjectFormEdit() {
    const form = document.getElementById( 'projectFormEdit' );
    if ( form ) {
        form.reset();
        form.classList.remove( 'was-validated' );
    }
    document.getElementById( 'projectIdEdit' ).value = '';
}

// ============ UTILITY FUNCTIONS ============

/**
 * Check if date is overdue
 */
function isOverdue( dueDate ) {
    if ( ! dueDate ) return false;
    const due = new Date( dueDate );
    const today = new Date();
    return due < today;
}

/**
 * Format date to YYYY-MM-DD
 */
function formatDate( dateString ) {
    if ( ! dateString ) return '';

    // Already in correct format
    if ( dateString.match( /^\d{4}-\d{2}-\d{2}$/ ) ) {
        return dateString;
    }

    const d = new Date( dateString );
    if ( isNaN( d ) ) return dateString;

    const yyyy = d.getFullYear();
    const mm = String( d.getMonth() + 1 ).padStart( 2, '0' );
    const dd = String( d.getDate() ).padStart( 2, '0' );

    return `${yyyy}-${mm}-${dd}`;
}

/**
 * Show/hide loading spinner
 */
function showSpinner( show ) {
    const spinner = document.getElementById( 'loadingSpinner' );
    if ( ! spinner ) return;

    if ( show ) {
        spinner.classList.remove( 'd-none' );
    } else {
        spinner.classList.add( 'd-none' );
    }
}

/**
 * Display notification toast
 */
function showNotification( message, type = 'info' ) {
    const container = document.getElementById( 'toastContainer' );
    if ( ! container ) return;

    const toastId = 'toast-' + Date.now();
    const bgClass = {
        'success': 'bg-success',
        'danger': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    }[type] || 'bg-info';

    const iconClass = {
        'success': 'fa-check-circle',
        'danger': 'fa-exclamation-triangle',
        'warning': 'fa-exclamation-circle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';

    const toastHTML = `
        <div id="${toastId}" class="toast alert alert-${type} alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas ${iconClass} me-2"></i>
                <strong class="me-auto">${type.charAt( 0 ).toUpperCase() + type.slice( 1 )}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div>${escapeHtml( message )}</div>
        </div>
    `;

    container.insertAdjacentHTML( 'beforeend', toastHTML );

    const toastElement = document.getElementById( toastId );
    const toast = new bootstrap.Toast( toastElement );
    toast.show();

    toastElement.addEventListener( 'hidden.bs.toast', function() {
        toastElement.remove();
    });
}

/**
 * Escape HTML special characters
 */
function escapeHtml( text ) {
    if ( ! text ) return '';
    const div = document.createElement( 'div' );
    div.textContent = text;
    return div.innerHTML;
}

// ============ GLOBAL FUNCTION EXPORTS ============

// Make functions available globally for onclick handlers
window.openCreateProjectModal = openCreateProjectModal;
window.openEditProjectModal = openEditProjectModal;
window.deleteProject = deleteProject;
window.openCreateTaskModal = openCreateTaskModal;
window.openEditTaskModal = openEditTaskModal;
window.deleteTask = deleteTask;
window.refreshTasks = refreshTasks;
window.loadProjects = loadProjects;
window.loadTasks = loadTasks;

