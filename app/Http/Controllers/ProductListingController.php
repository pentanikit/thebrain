<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        // Category filter (multiple allowed)
        if ($request->filled('category')) {
            $categoryIds = (array) $request->get('category');
            $query->whereIn('category_id', $categoryIds);
        }

        // Sub-category filter (multiple allowed)
        if ($request->filled('sub_category')) {
            $subCategoryIds = (array) $request->get('sub_category');
            $query->whereIn('sub_category_id', $subCategoryIds);
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->get('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->get('max_price'));
        }

        // Availability / Status filter
        // Expecting a column like 'stock_status' with values 'in_stock' / 'out_of_stock'
        $availability = $request->get('availability') ?? $request->get('status');
        if ($availability === 'in') {
            $query->where('stock_status', 'in_stock');
        } elseif ($availability === 'out') {
            $query->where('stock_status', 'out_of_stock');
        }

        // Sorting
        $sort = $request->get('sort', 'price_asc');
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

        // Per page
        $allowedPerPage = [12, 24, 30, 60];
        $perPage = (int) $request->get('show', 30);
        if (! in_array($perPage, $allowedPerPage)) {
            $perPage = 30;
        }

        $products = $query->paginate($perPage)->appends($request->query());

        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('frontend.pages.category', [
            'products'   => $products,
            'categories' => $categories,
            'perPage'    => $perPage,
        ]);
    }


    // ---------- CATEGORY-WISE LISTING ----------
    public function category(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        // Base query: at least this category
        $query = Product::query()
            ->with('category')
            ->where('category_id', $category->id);

        // If user also selects extra categories via filter, override base
        if ($request->filled('category')) {
            $categoryIds = (array) $request->get('category');
            $query->whereIn('category_id', $categoryIds);
        }

        // Sub-category filter (multiple allowed)
        if ($request->filled('sub_category')) {
            $subCategoryIds = (array) $request->get('sub_category');
            $query->whereIn('sub_category_id', $subCategoryIds);
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->get('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->get('max_price'));
        }

        // Availability / Status filter
        $availability = $request->get('availability') ?? $request->get('status');
        if ($availability === 'in') {
            $query->where('stock_status', 'in_stock');
        } elseif ($availability === 'out') {
            $query->where('stock_status', 'out_of_stock');
        }

        // Sorting
        $sort = $request->get('sort', 'price_asc');
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

        // Per page
        $allowedPerPage = [12, 24, 30, 60];
        $perPage = (int) $request->get('show', 30);
        if (! in_array($perPage, $allowedPerPage)) {
            $perPage = 30;
        }

        $products = $query->paginate($perPage)->appends($request->query());

        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        return view('frontend.pages.category', [
            'products'        => $products,
            'categories'      => $categories,
            'perPage'         => $perPage,
            'currentCategory' => $category,  // for dynamic heading + form action + default category
        ]);
    }

}
