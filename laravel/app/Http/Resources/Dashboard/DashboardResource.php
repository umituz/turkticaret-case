<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for transforming Dashboard data.
 * 
 * Handles the transformation of dashboard analytics data into standardized
 * JSON API responses. Includes statistical metrics and recent activity logs
 * for administrative dashboards.
 *
 * @package App\Http\Resources\Dashboard
 */
class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the dashboard resource
     */
    public function toArray(Request $request): array
    {
        return [
            'stats' => $this->resource['stats'],
            'recent_activity' => $this->resource['recent_activity']
        ];
    }
}