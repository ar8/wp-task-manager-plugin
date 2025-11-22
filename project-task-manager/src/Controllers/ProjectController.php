<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use TaskManager\Requests\ProjectRequest;
use TaskManager\Resources\ProjectResource;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return Project::with('tasks')->get();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = ProjectRequest::validateStore($request->all());

        if ( ! $validated['passes'] ) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validated['errors']
            ], 422);
        }

        $project_id = Project::create( $validated['validated'] );
        $project = Project::find( $project_id );

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data'    => ProjectResource::make( $project ),
        ]);
    
    }


    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return $project->load('tasks');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  Project $project)
    {
       $validated = ProjectRequest::validateUpdate($request->all());

       if ( ! $validated['passes'] ) {
           return response()->json([
               'message' => 'Validation failed',
               'errors' => $validated['errors']
           ], 422);
       }

       $project->update($validated['validated']);

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => $project->fresh() // Get fresh data from database
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ], 200);
    }
}
