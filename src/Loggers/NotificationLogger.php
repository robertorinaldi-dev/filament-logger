<?php

namespace Z3d0X\FilamentLogger\Loggers;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Activitylog\ActivityLogStatus;
use Spatie\Activitylog\ActivityLogger;

class NotificationLogger
{
    /**
     * Log the notification
     *
     * @param  NotificationSent|NotificationFailed  $event
     * @return void
     */
    public function handle(NotificationSent|NotificationFailed $event)
    {
        $notification = class_basename($event->notification);
        $receipent = $this->getRecipient($event->notifiable, $event->channel);

        if ($event instanceof NotificationSent) {
            $description = $notification.' Notification sent to '.$receipent;
        } else {
            $description = $notification.' Notification failed to '.$receipent;
        }

        app(ActivityLogger::class)
            ->useLog(config('filament-logger.notifications.log_name'))
            ->setLogStatus(app(ActivityLogStatus::class))
            ->causedByAnonymous()
            ->event(Str::of(class_basename($event))->headline())
            ->log($description);
    }

    public function getRecipient(AnonymousNotifiable|Notifiable $notifiable, string $channel): string
    {
        return $notifiable->routeNotificationFor($channel);
    }
}