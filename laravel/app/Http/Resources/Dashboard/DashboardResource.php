<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'stats' => $this->resource['stats'],
            'recent_activity' => $this->resource['recent_activity'],
            'system_status' => $this->resource['system_status']
        ];
    }
}