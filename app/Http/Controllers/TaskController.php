<?php

// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $task = Task::all();
        return view('tasks.index', compact('task'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:task,name',
        ]);
        $task = Task::create(['name' => $request->name]);
        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {
        $task->completed = $request->completed;
        $task->save();
        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }

    public function filter($status)
    {
        if ($status == 'completed') {
            $tasks = Task::where('completed', true)->get();
        } elseif ($status == 'incomplete') {
            $tasks = Task::where('completed', false)->get();
        } else {
            $tasks = Task::all();
        }

        return response()->json($tasks);
    }

}