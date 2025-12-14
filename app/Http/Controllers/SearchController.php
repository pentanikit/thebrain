<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $products = collect();

        if ($q !== '') {
            $products = Product::query()
                ->with([
                    'category:id,name,slug',
                    'subCategory:id,name,slug',
                    'childCategory:id,name,slug',
                    // If you have relation for descriptions:
                    'descriptions:id,product_id,body,sort_order',
                ])
                ->where(function ($qry) use ($q) {
                    $qry->where('products.name', 'like', "%{$q}%")
                        ->orWhere('products.sku', 'like', "%{$q}%")

                        // Category/Sub/Child names
                        ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('subCategory', fn($s) => $s->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('childCategory', fn($ch) => $ch->where('name', 'like', "%{$q}%"))

                        // Product descriptions table
                        ->orWhereHas('descriptions', fn($d) => $d->where('body', 'like', "%{$q}%"));
                })
                ->latest()
                ->paginate(24);
        }

        return view('frontend.pages.search-results', compact('products', 'q'));
    }

    public function suggest(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if ($q === '' || Str::length($q) < 2) {
            return response()->json(['q' => $q, 'items' => []]);
        }

        $items = Product::query()
            ->with([
                'category:id,name,slug',
                'subCategory:id,name,slug',
                'childCategory:id,name,slug',
                'descriptions:id,product_id,body,sort_order',
            ])
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.thumbnail',
                'products.category_id',
                'products.sub_category_id',
                'products.child_category_id',
            ])
            ->where(function ($qry) use ($q) {
                $qry->where('products.name', 'like', "%{$q}%")
                    ->orWhere('products.sku', 'like', "%{$q}%")

                    ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('subCategory', fn($s) => $s->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('childCategory', fn($ch) => $ch->where('name', 'like', "%{$q}%"))

                    ->orWhereHas('descriptions', fn($d) => $d->where('body', 'like', "%{$q}%"));
            })
            // Boost: name starts with q, sku starts with q
            ->orderByRaw("
                CASE
                    WHEN products.name LIKE ? THEN 0
                    WHEN products.sku  LIKE ? THEN 1
                    ELSE 2
                END
            ", ["{$q}%", "{$q}%"])
            ->limit(8)
            ->get()
            ->map(function ($p) {
                $cat   = $p->category?->name;
                $sub   = $p->subCategory?->name;
                $child = $p->childCategory?->name;

                // Get first description (sort_order 0 usually)
                $descBody = optional($p->descriptions->sortBy('sort_order')->first())->body;
                $descBody = Str::limit(strip_tags((string) $descBody), 70);

                $thumb = null;
                if (!empty($p->thumbnail)) {
                    // you store: products/thumbnails or products/gallery on 'public' disk
                    $thumb = asset('storage/' . ltrim($p->thumbnail, '/'));
                }

                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'sku'           => $p->sku,
                    'category'      => $cat,
                    'sub_category'  => $sub,
                    'child_category'=> $child,
                    'desc'          => $descBody,
                    'thumb'         => $thumb,

                    // Update this URL to your actual product details route
                    'url' => url('product-details/' .  $p->id),
                ];
            });

        return response()->json(['q' => $q, 'items' => $items]);
    }
}
