<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //return TaskResource::collection(Task::all());

        $tasks = $request->user()->tasks()
        ->latest()
        ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /*
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id'
        ]);

        $task = Task::create($validated);
        
        return response()->json([
            'message' => 'Task created successfully!',
            'data' => $task
        ], 201);
        */
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
        ]);
        $task = $request->user()->tasks()->create($validated);
        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        if ($request->user()->id !== $task->user_id) {
            return response()->json(['message' => 'You are not authorized to show this task'], 403);
        }

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($request->user()->id !== $task->user_id) {
            return response()->json(['message' => 'You are not authorized to update this task'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|string',
        ]);

        $task->update($validated);

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if ($request->user()->id !== $task->user_id) {
            return response()->json(['message' => 'You are not authorized to delete this task'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'task deleted']);
    }
}
