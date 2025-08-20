<?php

namespace App\Http\Controllers;

class HealthController extends BaseController
{
    public function health()
    {
        return $this->ok([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ], 'Service is healthy');
    }
}
