<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Setting\SettingUpdateRequest;
use App\Services\Setting\SettingsService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for System Settings management.
 * 
 * Handles system-wide configuration settings including retrieving,
 * updating individual settings, and monitoring system status.
 *
 * @package App\Http\Controllers\Setting
 */
class SettingsController extends BaseController
{
    /**
     * Create a new SettingsController instance.
     *
     * @param SettingsService $settingsService The settings service for configuration operations
     */
    public function __construct(protected SettingsService $settingsService) {}

    /**
     * Retrieve all active system settings.
     *
     * @return JsonResponse JSON response containing all active system settings
     */
    public function index(): JsonResponse
    {
        $settings = $this->settingsService->getAllActiveSettings();

        return $this->ok($settings, 'Settings retrieved successfully.');
    }

    /**
     * Update a specific system setting.
     *
     * @param SettingUpdateRequest $request The validated request containing setting key and value
     * @return JsonResponse JSON response confirming setting update
     */
    public function update(SettingUpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $this->settingsService->updateSetting($validated['key'], $validated['value']);

        return $this->ok(null, 'Setting updated successfully.');
    }

    /**
     * Get comprehensive system status information.
     *
     * @return JsonResponse JSON response containing system status and health metrics
     */
    public function status(): JsonResponse
    {
        $status = $this->settingsService->getSystemStatus();

        return $this->ok($status, 'System status retrieved successfully.');
    }
}
