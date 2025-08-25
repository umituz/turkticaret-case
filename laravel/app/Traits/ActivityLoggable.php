<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * ActivityLoggable trait for model activity logging.
 * 
 * Provides comprehensive activity logging functionality for model events
 * including create, update, delete, and restore operations. Captures user
 * information, model changes, and timestamps for audit trail purposes.
 *
 * @package App\Traits
 */
trait ActivityLoggable
{
    /**
     * Log activity for a model event.
     * 
     * Records model events with comprehensive metadata including user information,
     * model changes, and timestamps. Uses the Spatie Activity Log package for
     * persistent activity tracking with error handling.
     *
     * @param Model $model The model instance that triggered the event
     * @param string $event The event name (created, updated, deleted, restored)
     * @param array $changes Array of model attribute changes (for update events)
     * @return void
     */
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
        }
    }

    /**
     * Get comprehensive information about the current user.
     * 
     * Collects user metadata for activity logging including user identification,
     * IP address, and user agent information. Returns empty array if no user
     * is authenticated.
     *
     * @return array User information array with ID, name, email, IP, and user agent
     */
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

    /**
     * Generate a human-readable log description for the model event.
     * 
     * Creates descriptive log messages based on the model type and event.
     * Uses the model's UUID if available, falling back to ID or 'unknown'.
     *
     * @param Model $model The model instance
     * @param string $event The event name
     * @return string Human-readable log description
     */
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
