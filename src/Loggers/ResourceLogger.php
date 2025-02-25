<?php

namespace Z3d0X\FilamentLogger\Loggers;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\ActivityLogStatus;

class ResourceLogger
{
    public function created(Model $model)
    {
        $this->log($model, 'Created', attributes:$model->getAttributes());
    }
    
    public function updated(Model $model)
    {
        $changes = $model->getChanges();

        //Ignore the changes to remember_token
        if (count($changes) === 1 && array_key_exists('remember_token', $changes)) {
            return;
        }
        
        $this->log($model, 'Updated', attributes:$changes);
    }

    public function deleted(Model $model)
    {
        $this->log($model, 'Deleted');
    }

    private function log(Model $model, string $event, ?string $description = null, mixed $attributes = null)
    {
        if(is_null($description)) {
            $description = $this->getModelName($model).' '.$event;
        }

        if (auth()->check()) {
            $description .= ' by '.$this->getUserName(auth()->user());
        }

        $this->activityLogger()
            ->event($event)
            ->performedOn($model)
            ->withProperties($attributes)
            ->log($description);
    }

    protected function getUserName(?Model $user): string
    {
        if(blank($user)) {
            return 'Anonymous';
        }

        return Filament::getUserName($user);
    }

    protected function getModelName(Model $model)
    {
        return Str::of(class_basename($model))->headline();
    }

    protected function activityLogger(string $logName = null): ActivityLogger
    {
        $defaultLogName = config('filament-logger.resources.log_name');

        $logStatus = app(ActivityLogStatus::class);

        return app(ActivityLogger::class)
            ->useLog($logName ?? $defaultLogName)
            ->setLogStatus($logStatus);
    }
}
