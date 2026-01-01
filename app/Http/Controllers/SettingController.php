<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    /**
     * Display the settings page
     *
     * @return Response
     */
    public function index(): Response
    {
        $settings = SiteSetting::getAllGrouped();

        return Inertia::render('Settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        // Get all settings to validate against
        $allSettings = SiteSetting::all()->keyBy('key');

        // Build validation rules dynamically based on setting types
        $rules = [];
        foreach ($allSettings as $key => $setting) {
            $rule = match ($setting->type) {
                'boolean' => 'required|boolean',
                'integer' => 'required|integer',
                'float' => 'required|numeric',
                'array', 'json' => 'required|array',
                default => 'required|string',
            };
            $rules[$key] = $rule;
        }

        // Validate the request
        $validated = $request->validate($rules);

        // Update each setting
        foreach ($validated as $key => $value) {
            SiteSetting::set($key, $value);
        }

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
