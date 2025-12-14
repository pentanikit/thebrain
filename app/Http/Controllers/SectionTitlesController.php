<?php
namespace App\Http\Controllers;

use App\Models\SectionTitles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SectionTitlesController extends Controller
{
    public function index()
    {
        $titles = SectionTitles::orderBy('category_type')->get();
    
        return view('backend.sectiontitles.sectiontitles', compact('titles'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_type' => 'required|string|max:255',
            'section_title' => 'required|string|max:255',
            'key'           => 'required|string|max:255',
            'value'         => 'required|string',
        ]);

        SectionTitles::updateOrCreate(
            [
                'category_type' => $validated['category_type'],
                'section_title' => $validated['section_title'],
                'key'           => $validated['key'],
            ],
            [
                'value'         => $validated['value'],
            ]
        );

        // Cache::forget("section_title.{$title->category_type}.{$title->key}");

        return back()->with('success', 'Section title saved successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_type' => 'required|string|max:255',
            'section_title' => 'required|string|max:255',
            'key'           => 'required|string|max:255',
            'value'         => 'required|string',
        ]);

        $sectionTitle = SectionTitles::findOrFail($id);
        $sectionTitle->update($validated);
        // Cache::forget("section_title.{$title->category_type}.{$title->key}");
        return back()->with('success', 'Section title updated successfully.');
    }

    public function destroy($id)
    {
        SectionTitles::findOrFail($id)->delete();
        // Cache::forget("section_title.{$title->category_type}.{$title->key}");
        return back()->with('success', 'Section title deleted successfully.');
    }
}

