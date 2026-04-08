<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function index()
    {
        $this->authorize('manage_settings');

        $settings = Setting::all()->pluck('value', 'key');
        return $this->successResponse($settings, 'Settings retrieved');
    }

    public function update(Request $request)
    {
        $this->authorize('manage_settings');

        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:5000',
        ]);

        foreach ($data['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return $this->successResponse(Setting::all()->pluck('value', 'key'), 'Settings updated successfully.');
    }
}
