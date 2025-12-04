<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banners;
use Illuminate\Support\Facades\Storage;

class BannersController extends Controller
{
        /**
     * Keys we will use for 4 hero banners.
     */
    protected array $heroKeys = [
        1 => 'home_hero_1',
        2 => 'home_hero_2',
        3 => 'home_hero_3',
        4 => 'home_hero_4',
    ];

    /**
     * Show the hero banner upload form.
     */
    public function show()
    {
       
        $keys = ['banner_1', 'banner_2', 'banner_3', 'banner_4'];

        
        $banners = Banners::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        return view('backend.banners.banner-upload', compact('banners'));
    }


    /**
     * Handle upload / update of 4 hero banners.
     */
    public function update(Request $request)
    {
       
        $request->validate([
            'banner_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:7096'],
            'banner_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:7096'],
            'banner_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:7096'],
            'banner_4' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:7096'],
        ]);

        $keys = ['banner_1', 'banner_2', 'banner_3', 'banner_4'];

        foreach ($keys as $key) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);

                
                $filename = $key . '_' . time() . '.' . $file->getClientOriginalExtension();

               
                $path = $file->storeAs('banners', $filename, 'public'); 
                
                $existing = Banners::where('key', $key)->first();
                if ($existing && $existing->value) {
                    Storage::disk('public')->delete($existing->value);
                }

                
                Banners::updateOrCreate([
                    'key' => $key,
                    'value' => $path
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Banners updated successfully.');
    }
}
