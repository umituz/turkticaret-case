<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Setting\SettingUpdateRequest;
use App\Services\Setting\SettingsService;
use Illuminate\Http\JsonResponse;

class SettingsController extends BaseController
{
    public function __construct(protected SettingsService $settingsService) {}

    public function index(): JsonResponse
    {
        $settings = $this->settingsService->getAllActiveSettings();

        return $this->ok($settings, 'Settings retrieved successfully.');
    }

    public function update(SettingUpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $this->settingsService->updateSetting($validated['key'], $validated['value']);

        return $this->ok(null, 'Setting updated successfully.');
    }

    public function status(): JsonResponse
    {
        $status = $this->settingsService->getSystemStatus();

        return $this->ok($status, 'System status retrieved successfully.');
    }
}
