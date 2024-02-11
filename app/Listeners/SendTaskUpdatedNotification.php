<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskUpdatedNotification;
use App\Models\User;
use Illuminate\Mail\Mailable;
class SendTaskUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\TaskUpdated  $event
     * @return void
     */
    public function handle(TaskUpdated $event)
    {
        // Retrieve the current user from the task
        $user = User::find($event->task->user_id);

        // Send the email
        Mail::send('eventMail',$user, function($message) use ($user){
            $message->to($user['email']);
            $message->subject('Task Updated');
        });
    }
}
