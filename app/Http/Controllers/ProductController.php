<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
        //
    }



        /**
     * Main listing + search + filter page
     * URL: /products?q=...&min_price=...&brand=... ইত্যাদি
     */
    public function filter(Request $request)
    {
        $query = Product::query()->with('category'); // চাইলে images/brand relation ও load করতে পারো

        // Common filters apply
        $this->applyFilters($query, $request);

        // Sort & pagination
        [$sort, $perPage] = $this->resolveSortAndPerPage($request);
        $this->applySort($query, $sort);

        $products = $query->paginate($perPage)->withQueryString();

        // UI header text dynamic
        $pageTitle = $request->filled('q')
            ? 'Search result for: "'.$request->q.'"'
            : 'Fashionable caps (2025 Update)';

        // Filter dropdown এর জন্য ডাটা (brand/size/color এগুলো তোমার টেবিল অনুযায়ী)
        // $brands = Product::select('brand')->distinct()->pluck('brand');
        // $sizes  = Product::select('size')->distinct()->pluck('size');
        // $colors = Product::select('color')->distinct()->pluck('color');

        return view('frontend.pages.category', [
            'products'      => $products,
            'pageTitle'     => $pageTitle,
            'currentCategory' => null,
            // 'brands'        => $brands,
            // 'sizes'         => $sizes,
            // 'colors'        => $colors,
            'sort'          => $sort,
            'perPage'       => $perPage,
            'filters'       => [
                'q'          => $request->q,
                'min_price'  => $request->min_price,
                'max_price'  => $request->max_price,
                'availability' => $request->availability,
                'brand'      => $request->brand,
                'size'       => $request->size,
                'color'      => $request->color,
            ],
        ]);
    }

    /**
     * Category wise listing
     * URL: /category/{slug}?q=...&min_price=...
     */
    public function category(Category $category, Request $request)
    {
        // Category এর product relation ধরে নিচ্ছি ->products() আছে
        $query = $category->products()->with('category');

        $this->applyFilters($query, $request);

        [$sort, $perPage] = $this->resolveSortAndPerPage($request);
        $this->applySort($query, $sort);

        $products = $query->paginate($perPage)->withQueryString();

        $pageTitle = $category->name . ' Price in Bangladesh';

        // $brands = $category->products()->select('brand')->distinct()->pluck('brand');
        // $sizes  = $category->products()->select('size')->distinct()->pluck('size');
        // $colors = $category->products()->select('color')->distinct()->pluck('color');

        return view('frontend.pages.category', [
            'products'      => $products,
            'pageTitle'     => $pageTitle,
            'currentCategory' => $category,
            // 'brands'        => $brands,
            // 'sizes'         => $sizes,
            // 'colors'        => $colors,
            'sort'          => $sort,
            'perPage'       => $perPage,
            'filters'       => [
                'q'          => $request->q,
                'min_price'  => $request->min_price,
                'max_price'  => $request->max_price,
                'availability' => $request->availability,
                'brand'      => $request->brand,
                'size'       => $request->size,
                'color'      => $request->color,
            ],
        ]);
    }

    /**
     * Common filter logic
     */
    protected function applyFilters(Builder $query, Request $request): void
    {
        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->max_price);
        }

        // Availability
        if ($request->availability === 'in') {
            $query->where('stock_status', 'in_stock');
        } elseif ($request->availability === 'out') {
            $query->where('stock_status', 'out_of_stock');
        }

        // Brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Size
        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        // Color
        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }
    }

    /**
     * sort + per_page resolve
     */
    protected function resolveSortAndPerPage(Request $request): array
    {
        $allowedPerPage = [12, 24, 30, 60];

        $perPage = (int) $request->get('show', 30);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 30;
        }

        $sort = $request->get('sort', 'price_asc'); // default

        return [$sort, $perPage];
    }

    /**
     * Query তে sort apply
     */
    protected function applySort(Builder $query, string $sort): void
    {
        switch ($sort) {
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'price_asc':
            default:
                $query->orderBy('price', 'asc');
                break;
        }
    }








}
