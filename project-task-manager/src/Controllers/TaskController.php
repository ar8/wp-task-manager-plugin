<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

// TODO: Use Form Requests for validations
// TODO use API Resources for response formatting

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks
     */
    public function index(Request $request)
    {
        $query = Task::with('project');
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return $query->get();
    }

    /**
     * create a task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $task = Task::create($validated);   

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task->load('project')
        ], 201); 
    }

    /**
     * Display the specified task
     */
    public function show(Task $task)
    {
        return $task->load('project');   
    }

    /**
     * Update a task
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'task_name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'priority' => 'sometimes|nullable|in:low,medium,high',
            'due_date' => 'sometimes|nullable|date',
            'status' => 'sometimes|nullable|in:pending,in_progress,completed',
            'project_id' => 'sometimes|nullable|exists:projects,id',
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task->load('project')
        ], 200);

    }


    /**
     * Delete a task
     */
    public function destroy(Task $task)
    {
    
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ], 200);
    }

    /**
     * Get tasks by project
     */
    public function getByProject(Project $project)
    {
        return $project->tasks;
    }

    /**
     * Get all projects (id and name only) for dropdowns
     */
    public function getProjects()
    {
        return Project::select('id', 'project_name')->get();
    }
}
