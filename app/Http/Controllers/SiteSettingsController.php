<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSettings::orderBy('group')->get()
            ->groupBy('group');

        return view('backend.settings.site-setting', compact('settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string|max:50',
            'key'   => 'required|string|max:100',
            'type'  => 'required|string',
            'value' => 'nullable',
        ]);

        if ($request->type === 'image' && $request->hasFile('value')) {
            $validated['value'] = $request->file('value')
                ->store('site-settings', 'public');
        }

        $setting = SiteSettings::create($validated);

        Cache::forget("site_setting.{$setting->key}");

        return back()->with('success', 'Setting added successfully.');
    }

    public function update(Request $request, $id)
    {
        $setting = SiteSettings::findOrFail($id);

        Cache::forget("site_setting.{$setting->key}");

        $validated = $request->validate([
            'group' => 'required|string|max:50',
            'key'   => 'required|string|max:100',
            'type'  => 'required|string',
            'value' => 'nullable',
        ]);

        if ($request->type === 'image' && $request->hasFile('value')) {
            if ($setting->value) {
                Storage::disk('public')->delete($setting->value);
            }

            $validated['value'] = $request->file('value')
                ->store('site-settings', 'public');
        }

        $setting->update($validated);

        Cache::forget("site_setting.{$setting->key}");

        return back()->with('success', 'Setting updated successfully.');
    }

    public function destroy($id)
    {
        $setting = SiteSettings::findOrFail($id);

        if ($setting->type === 'image' && $setting->value) {
            Storage::disk('public')->delete($setting->value);
        }

        Cache::forget("site_setting.{$setting->key}");

        $setting->delete();

        return back()->with('success', 'Setting deleted successfully.');
    }
}
