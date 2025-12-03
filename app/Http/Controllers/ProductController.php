<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\ProductDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query()->with(['category', 'subCategory']);

        // Search by name or SKU
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter (match parent OR sub OR child)
        if ($categoryId = $request->input('category_id')) {
            $query->where(function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                ->orWhere('sub_category_id', $categoryId)
                ->orWhere('child_category_id', $categoryId);
            });
        }

        // Sort
        switch ($request->input('sort')) {
            case 'cheap':
                // use effective price: offer_price -> price -> old_price
                $query->orderByRaw('COALESCE(offer_price, price, old_price) ASC');
                break;

            case 'expensive':
                $query->orderByRaw('COALESCE(offer_price, price, old_price) DESC');
                break;

            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(20)->withQueryString(); // keep filters in pagination links

        return view('backend.products.products', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.products.addproduct');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:products,slug',
            'sku'               => 'nullable|string|max:255|unique:products,sku',

            'category_id'       => 'required|exists:categories,id',
            'sub_category_id'   => 'nullable|exists:categories,id',
            'child_category_id' => 'nullable|exists:categories,id',

            'price'             => 'required|integer|min:0',
            'old_price'         => 'nullable|integer|min:0',
            'offer_price'       => 'nullable|integer|min:0',

            'stock_quantity'    => 'required|integer|min:0',
            'stock_status'      => 'required|in:in_stock,out_of_stock,preorder',

            'description'       => 'nullable|string',

            'thumbnail'         => 'nullable|image|max:2048',
            'images.*'          => 'nullable|image|max:4096',

            'spec_key.*'        => 'nullable|string|max:255',
            'spec_value.*'      => 'nullable|string|max:1000',
        ]);

        $action = $request->input('action', 'publish'); // publish|draft

        $product = DB::transaction(function () use ($request, $validated, $action) {
            // Generate unique slug
            $slug = $validated['slug'] ?? Str::slug($validated['name']);
            $baseSlug = $slug;
            $i = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            // Create product
            $product = Product::create([
                'category_id'       => $validated['category_id'],
                'sub_category_id'   => $validated['sub_category_id'] ?? null,
                'child_category_id' => $validated['child_category_id'] ?? null,

                'name'              => $validated['name'],
                'slug'              => $slug,
                'sku'               => $validated['sku'] ?? null,

                'price'             => $validated['price'],
                'old_price'         => $validated['old_price'] ?? null,
                'offer_price'       => $validated['offer_price'] ?? null,

                'stock_quantity'    => $validated['stock_quantity'],
                'stock_status'      => $validated['stock_status'],

                'thumbnail'         => null, // will update below if uploaded
                'is_active'         => $action === 'publish',
            ]);

            // === Handle thumbnail upload ===
            $thumbPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbPath = $request->file('thumbnail')->store('products/thumbnails', 'public');
                $product->update(['thumbnail' => $thumbPath]);
            }

            // === Handle gallery images ===
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    if (!$file->isValid()) {
                        continue;
                    }

                    $path = $file->store('products/gallery', 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path'       => $path,
                        'alt_text'   => $product->name,
                    ]);

                    // If we don't have thumbnail yet, use first gallery image
                    if ($index === 0 && !$thumbPath) {
                        $thumbPath = $path;
                        $product->update(['thumbnail' => $thumbPath]);
                    }
                }
            }

            // === Specifications (json) ===
            $specKeys   = $request->input('spec_key', []);
            $specValues = $request->input('spec_value', []);
            $specArray  = [];

            foreach ($specKeys as $idx => $key) {
                $key = trim($key ?? '');
                $value = trim($specValues[$idx] ?? '');
                if ($key === '' && $value === '') {
                    continue;
                }
                $specArray[] = [
                    'key'   => $key,
                    'value' => $value,
                ];
            }

            if (!empty($specArray)) {
                ProductSpecification::create([
                    'product_id' => $product->id,
                    'value'      => $specArray, // will be saved as JSON
                ]);
            }

            // === Description ===
            if (!empty($validated['description'] ?? null)) {
                ProductDescription::create([
                    'product_id' => $product->id,
                    'body'       => $validated['description'],
                    'sort_order' => 0,
                ]);
            }

            return $product;
        });

        return redirect()
            ->back()
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
            $product->load([
                'descriptions',      // hasOne
                'specifications',   // hasMany
                'images',           // hasMany (gallery)
            ]);
        return view('backend.products.editproduct', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
