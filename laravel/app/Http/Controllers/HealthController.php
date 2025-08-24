<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Health Check Controller for API monitoring.
 *
 * Provides health check endpoints for monitoring and load balancer
 * health verification. Returns system status and timestamp information.
 *
 * @package App\Http\Controllers
 */
class HealthController extends BaseController
{
    /**
     * Check the health status of the API service.
     *
     * @return JsonResponse JSON response containing health status and timestamp
     */
    public function health()
    {
        return $this->ok([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ], 'Service is healthy');
    }
}
