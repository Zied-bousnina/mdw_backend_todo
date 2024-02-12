<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Mail\NewTask;
use App\Mail\updateTaskMail;
use App\Mail\WelcomeMail;
use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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

            $task = new Task();
            $task->title = $request->title;
            $task->description = $request->description;
            $task->due_date = Carbon::parse($request->due_date)->format('Y-m-d H:i:s');
            $task->remind_at = Carbon::parse($request->remind_at)->format('Y-m-d H:i:s');
            $task->status = $request->input('status', 'en attente');
            $task->user_id = auth()->user()->id;
            $task->save();

            try {
                Mail::to($request->user()->email)->send(new NewTask());
            } catch (\Throwable $th) {
                Log::error('Error sending email', ['user_id' => auth()->user()->id, 'error_message' => $th->getMessage()]);
            }

            Log::info('Task created successfully', ['user_id' => auth()->user()->id, 'task_id' => $task->id]);
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating task', ['user_id' => auth()->user()->id, 'error_message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating task',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }


            Cache::forget('task_' . $id);

            $task->delete();
            Log::info('Task deleted successfully', ['user_id' => auth()->user()->id, 'task_id' => $id]);

            return response()->json([
                'message' => 'Task deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting task', ['user_id' => auth()->user()->id, 'error_message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error deleting task',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $task = Cache::remember('task_' . $id, now()->addMinutes(30), function () use ($id) {
                return Task::find($id);
            });

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            // Update task properties if they exist in the request
            $task->title = $request->input('title', $task->title);
            $task->description = $request->input('description', $task->description);
            $task->status = $request->input('status', $task->status);
            $task->user_id = $request->input('user_id', $task->user_id);
            $task->completed_at = $request->input('completed_at', $task->completed_at);
            $dueDate = $request->input('due_date');
            if ($dueDate) {
                $task->due_date = Carbon::parse($dueDate)->format('Y-m-d H:i:s');
            }


            $task->save();
            try {
                Mail::to($request->user()->email)->send(new updateTaskMail(
                    $request->user(),
                    $task
                ));
            } catch (\Throwable $th) {
                Log::error('Error sending email', ['user_id' => auth()->user()->id, 'error_message' => $th->getMessage()]);
            }


            Log::info('Task updated successfully', ['user_id' => auth()->user()->id, 'task_id' => $task->id]);

            Cache::forget('task_' . $id);

            return response()->json([
                'task' => $task,
                'message' => 'Task updated successfully',
                'user' => $request->user(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating task', ['user_id' => auth()->user()->id, 'error_message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating task',
                'error' => $e->getMessage(),
            ]);
        }
    }





    public function getTaskStatistics( Request $request )
    {
        $user_id =auth()->user()->id; // Get the ID of the authenticated user

        $statistics = [
            'en_attente' => Task::where('user_id', $user_id)->where('status', 'en attente')->count(),
            'open' => Task::where('user_id', $user_id)->where('status', 'open')->count(),
            'in_progress' => Task::where('user_id', $user_id)->where('status', 'in progress')->count(),
            'accepted' => Task::where('user_id', $user_id)->where('status', 'Accepted')->count(),
            'solved' => Task::where('user_id', $user_id)->where('status', 'solved')->count(),
            'on_hold' => Task::where('user_id', $user_id)->where('status', 'on hold')->count(),
            'overdue' => Task::where('user_id', $user_id)->where('due_date', '<', now())->count(),
            'to_do' => Task::where('user_id', $user_id)->where('status', 'en attente')->count(),
            'open_tasks' => Task::where('user_id', $user_id)->where('status', 'open')->count(),
            'due_today' => Task::where('user_id', $user_id)->whereDate('due_date', now())->count(),
        ];

        return response()->json(['statistics' => $statistics]);
    }


    public function readWithSortBy(Request $request)
    {
        $user_id = auth()->id(); // Get the ID of the authenticated user
        $sortBy = $request->input('sort_by', 'title'); // Default to sorting by name if not specified
        $sortOrder = $request->input('sort_order', 'asc'); // Default to ascending order if not specified

        $tasks = Task::where('user_id', $user_id)->orderBy($sortBy, $sortOrder)->get();

        return response()->json(['tasks' => $tasks]);
    }



    public function show($id)
    {
        try {

            $task = Cache::remember('task_' . $id, now()->addMinutes(30), function () use ($id) {
                return Task::find($id);
            });

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            Log::info('Task fetched successfully', ['user_id' => auth()->user()->id, 'task_id' => $task->id]);

            return response()->json([
                'task' => $task
            ]);
        } catch (\Exception $e) {

            Log::error('Error fetching task', ['user_id' => auth()->user()->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Error fetching task',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // public function update(Request $request, $id)
    // {
    //     $task = Task::find($id);

    //     if (!$task) {
    //         return response()->json([
    //             'message' => 'Task not found'
    //         ], 404);
    //     }

    //     $task->update($request->all());

    //     return response()->json([
    //         'task' => $task,
    //         'message' => 'Task updated successfully'
    //     ]);
    // }



    // --------------------------------------------
    public function read(Request $request)
    {
        try {

            $user = $request->user();
            $tasks = Task::where('user_id', $user->id)->get();

            return response()->json([
                'tasks' => $tasks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tasks',
                'error' => $e->getMessage(),
            ]);
        }
    }



}
