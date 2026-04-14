<?php

namespace Marble\Admin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marble\Admin\Models\ItemTask;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public ItemTask $task) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $item     = $this->task->item;
        $assigner = $this->task->creator;
        $url      = route('marble.item.edit', $item) . '#collaboration';

        $message = (new MailMessage)
            ->subject('Task assigned: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($assigner?->name . ' assigned you a task on "' . $item->name() . '":')
            ->line('**' . $this->task->title . '**');

        if ($this->task->due_date) {
            $message->line('Due: ' . $this->task->due_date->format('d.m.Y'));
        }

        return $message->action('View Item', $url);
    }
}
