<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait ActivityLoggable
{
    protected function logActivity(Model $model, string $event, array $changes = []): void
    {
        try {
            $activity = activity('default')
                ->performedOn($model)
                ->event($event);

            if (Auth::check()) {
                $activity->causedBy(Auth::user());
            }

            $properties = [];
            if (!empty($changes)) {
                $properties['attributes'] = $changes;
            }

            $properties['user_info'] = $this->getUserInfo();
            $properties['timestamp'] = now()->toISOString();

            if (!empty($properties)) {
                $activity->withProperties($properties);
            }

            $logDescription = $this->getLogDescription($model, $event);

            $activity->log($logDescription);
        } catch (\Exception $e) {
            logger()->error('Activity logging failed', [
                'model' => get_class($model),
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function getUserInfo(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();

        return [
            'user_id' => $user->uuid ?? null,
            'user_name' => $user->name ?? 'Unknown',
            'user_email' => $user->email ?? 'unknown@example.com',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
    }

    protected function getLogDescription(Model $model, string $event): string
    {
        $modelName = class_basename($model);
        $modelId = $model->uuid ?? $model->id ?? 'unknown';

        return match($event) {
            'created' => "{$modelName} [{$modelId}] was created",
            'updated' => "{$modelName} [{$modelId}] was updated",
            'deleted' => "{$modelName} [{$modelId}] was deleted",
            'restored' => "{$modelName} [{$modelId}] was restored",
            default => "{$modelName} [{$modelId}] {$event} event occurred"
        };
    }
}
