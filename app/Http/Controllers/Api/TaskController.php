<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    //
    public function index()
    {
        return response()->json([
            'message' => 'Hello World'
        ]);
    }

    public function store(CreateTaskRequest $request)
    {
        try {
            // Validation is already handled by the form request (CreateTaskRequest)

            // Create a new task
            $task = new Task();
            $task->title = $request->title;
            $task->description = $request->description;
            $task->due_date = $request->due_date;
            $task->remind_at = $request->remind_at;
            // dd(auth()->user()->id);
            $task->user_id =auth()->user()->id;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'success' => false,
                'message' => 'Error creating task',
                'error' => $e->getMessage(),
            ]);
        }
    }






    public function show($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'task' => $task
        ]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $task->update($request->all());

        return response()->json([
            'task' => $task,
            'message' => 'Task updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    public function complete(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $task->completed = true;
        $task->completed_at = now();
        $task->save();

        return response()->json([
            'task' => $task,
            'message' => 'Task completed successfully'
        ]);
    }

    public function incomplete(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $task->completed = false;
        $task->completed_at = null;
        $task->save();

        return response()->json([
            'task' => $task,
            'message' => 'Task marked as incomplete'
        ]);
    }

    public function search(Request $request)
    {
        $tasks = $request->user()->tasks()->where('title', 'like', '%' . $request->search . '%')->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function all(Request $request)
    {
        $tasks = $request->user()->tasks()->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function completed(Request $request)
    {
        $tasks = $request->user()->tasks()->where('completed', true)->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function incompleteTasks(Request $request)
    {
        $tasks = $request->user()->tasks()->where('completed', false)->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function dueToday(Request $request)
    {
        $tasks = $request->user()->tasks()->whereDate('due_date', now())->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function dueTomorrow(Request $request)
    {
        $tasks = $request->user()->tasks()->whereDate('due_date', now()->addDay())->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function dueThisWeek(Request $request)
    {
        $tasks = $request->user()->tasks()->whereBetween('due_date', [now(), now()->addWeek()])->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }


}
