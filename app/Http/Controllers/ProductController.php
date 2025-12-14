<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\ProductDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

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
        // Load only relationships here
        $product->load([
            'images',
            'specifications',
            'descriptions',
            'category',
            'subCategory',
        ]);

        return view('frontend.pages.single-product', [
            'product' => $product,
        ]);
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
        // === Build validation rules ===
        $rules = [
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],

            'price'             => ['required', 'integer', 'min:0'],
            'old_price'         => ['nullable', 'integer', 'min:0'],
            'offer_price'       => ['nullable', 'integer', 'min:0'],

            'stock_quantity'    => ['required', 'integer', 'min:0'],
            'stock_status'      => ['required', 'in:in_stock,out_of_stock,preorder'],

            'category_id'       => ['required', 'exists:categories,id'],
            'sub_category_id'   => ['nullable', 'exists:categories,id'],
            'child_category_id' => ['nullable', 'exists:categories,id'],

            'thumbnail'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'images.*'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'spec_key.*'        => ['nullable', 'string', 'max:255'],
            'spec_value.*'      => ['nullable', 'string', 'max:255'],

            'action'            => ['required', 'in:publish,draft'],
        ];

        // slug & sku basic rules
        $rules['slug'] = ['nullable', 'string', 'max:255'];
        $rules['sku']  = ['nullable', 'string', 'max:255'];

        // Only check UNIQUE if value actually changed
        if ($request->filled('slug') && $request->slug !== $product->slug) {
            $rules['slug'][] = Rule::unique('products', 'slug');
        }

        if ($request->filled('sku') && $request->sku !== $product->sku) {
            $rules['sku'][] = Rule::unique('products', 'sku');
        }

        $validated = $request->validate($rules);

        // === Generate slug if empty ===
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // === Active / Draft ===
        $isActive = $validated['action'] === 'publish';
        unset($validated['action']);

        // === Thumbnail upload ===
        if ($request->hasFile('thumbnail')) {
            if (!empty($product->thumbnail)) {
                Storage::disk('public')->delete($product->thumbnail);
            }

            $validated['thumbnail'] = $request->file('thumbnail')
                ->store('products/thumbnails', 'public');
        }

        // === Only update changed fields ===
        $product->fill([
            'name'              => $validated['name'],
            'slug'              => $validated['slug'] ?? $product->slug,
            'sku'               => $validated['sku'] ?? $product->sku,
            'price'             => $validated['price'],
            'old_price'         => $validated['old_price'] ?? null,
            'offer_price'       => $validated['offer_price'] ?? null,
            'stock_quantity'    => $validated['stock_quantity'],
            'stock_status'      => $validated['stock_status'],
            'thumbnail'         => $validated['thumbnail'] ?? $product->thumbnail,
            'category_id'       => $validated['category_id'],
            'sub_category_id'   => $validated['sub_category_id'] ?? null,
            'child_category_id' => $validated['child_category_id'] ?? null,
            'is_active'         => $isActive,
        ]);

        // This automatically only updates dirty (changed) attributes
        $product->save();

        // === Description table (product_descriptions) ===
        // assumes: $product->descriptions() -> hasOne or hasMany with `body` column
        $descriptionText = $validated['description'] ?? null;

        if ($descriptionText !== null) {
            $product->descriptions()->updateOrCreate(
                ['product_id' => $product->id],
                ['body' => $descriptionText]
            );
        }

        // === Specifications JSON (e.g. product_specifications.specs) ===
        // From spec_key[] and spec_value[]
        $keys   = $request->input('spec_key', []);
        $values = $request->input('spec_value', []);
        $specs  = [];

        foreach ($keys as $index => $key) {
            $key   = trim($key ?? '');
            $value = trim($values[$index] ?? '');

            // Skip completely empty rows
            if ($key === '' && $value === '') {
                continue;
            }

            $specs[] = [
                'key'   => $key,
                'value' => $value,
            ];
        }

        // assumes: $product->specifications() -> hasOne(ProductSpecification::class)
        // and ProductSpecification has `specs` JSON column with cast ['specs' => 'array']
        if (count($specs)) {
            $product->specifications()->updateOrCreate(
                ['product_id' => $product->id],
                ['value' => $specs]
            );
        } else {
            // if you want to delete specs when everything is empty:
            $product->specifications()->delete();
        }

        // === Gallery images (product_images table) ===
        // assumes: $product->images() -> hasMany(ProductImage::class) with `path` column
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if (!$file) {
                    continue;
                }

                $path = $file->store('products/gallery', 'public');

                $product->images()->create([
                    'path' => $path,
                ]);
            }
        }

        // === Redirect back ===
        return redirect()
            ->back() // change if your index route name is different
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
       

        try {
            $product = Product::with([
                'images',
                'specifications',
                'descriptions',
            ])->findOrFail($product->id);

            // === Delete thumbnail from storage ===
            if ($product->thumbnail && Storage::disk('public')->exists($product->thumbnail)) {
                Storage::disk('public')->delete($product->thumbnail);
            }

            // === Delete gallery images from storage ===
            if ($product->images && $product->images->count()) {
                foreach ($product->images as $image) {
                    if ($image->path && Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                    }
                }
            }

            // === Delete related DB records ===
            $product->images()->delete();
            $product->specifications()->delete();
            $product->descriptions()->delete();

            // === Delete product ===
            $product->delete();

          

            return redirect()->back()->with('success', 'Product deleted successfully.');
        } catch (\Throwable $e) {
          return $e;

            return redirect()->back()->with('error', 'Failed to delete product.');
        }
    }



}
