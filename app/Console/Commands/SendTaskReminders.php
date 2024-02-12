<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:task-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for tasks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
{
    $tasks = Task::whereBetween('due_date', [now(), now()->addSeconds(1)])->get();

    foreach ($tasks as $task) {
        $title = 'Task Reminder';
        $body = "Don't forget to complete the task: {$task->title}";

        $user = User::find($task->user_id);

        if ($user) {
            Mail::to($user->email)->send(new \App\Mail\TaskReminderMail(
                $title,
                $body,
                $task
            ));
        }
    }

    $this->info('Task reminders sent successfully!');
}

}
