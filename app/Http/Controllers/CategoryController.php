<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backend.categories.categories');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    public function children($parentId)
    {
        // use your existing helper
        $children = categories((int) $parentId);

        // return minimal JSON
        return response()->json(
            $children->map(function ($cat) {
                return [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                ];
            })
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:categories,slug',
            'parent_id'  => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active'  => 'nullable|boolean',
            'thumbnail'  => 'nullable|image|max:4048',
        ]);

        // Generate slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // is_active checkbox
        $data['is_active'] = $request->boolean('is_active');

        // Determine level based on parent
        if (!empty($data['parent_id'])) {
            $parent = Category::find($data['parent_id']);
            $data['level'] = ($parent?->level ?? 1) + 1;
        } else {
            $data['level'] = 1;
        }

        // Default sort_order if null
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        // Thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()
            ->back()
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
    }


    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        $categories = Category::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('slug', 'like', "%{$q}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Return ONLY the rows (HTML) so JS can drop it into <tbody>
        return view('backend.categories._rows', compact('categories'))->render();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // If the category has an image, delete it from the disk
        if (!empty($category->thumbnail)) {
            // using 'public' disk (storage/app/public)
            if (Storage::disk('public')->exists($category->thumbnail)) {
                Storage::disk('public')->delete($category->thumbnail);
            }
        }

        // Finally delete the category record
        $category->delete();

        return redirect()
            ->back()
            ->with('success', 'Category deleted successfully!');
    }
}
