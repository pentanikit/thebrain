<?php

use App\Models\Category;
use App\Models\Banners;
use Illuminate\Support\Facades\Storage;

if (! function_exists('categories')) {
    /**
     * Get active categories.
     *
     * Usage:
     *  - categories()                  → all active categories
     *  - categories(null)              → only root (parent) categories
     *  - categories($parentId)         → children of a specific category
     */
    function categories(?int $parentId = null)
    {
        return Category::query()
            ->when(
                is_null($parentId),
                fn ($q) => $q->whereNull('parent_id'),   // root level categories
                fn ($q) => $q->where('parent_id', $parentId) // child categories
            )
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}

if (! function_exists('category_tree')) {
    /**
     * Get full nested category tree (parent → children → grandchildren).
     * Useful for menus / filters.
     */
    function category_tree()
    {
        $all = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('parent_id');

        $buildTree = function ($parentId) use (&$buildTree, $all) {
            return ($all[$parentId] ?? collect())->map(function ($cat) use (&$buildTree, $all) {
                $cat->children = $buildTree($cat->id);
                return $cat;
            });
        };

        // root = parent_id null
        return $buildTree(null);
    }
}


if (! function_exists('bn_digits')) {
    /**
     * Convert English digits to Bangla digits.
     */
    function bn_digits(string $number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];

        return str_replace($en, $bn, $number);
    }
}

if (! function_exists('currency')) {

    function currency($amount, ?string $currency = null, array $options = []): string
    {
        if ($amount === null) {
            return '';
        }

        $currency = $currency ?: config('currency.default', 'bdt');
        $cfg      = config("currency.$currency");

        $amount = (float) $amount;

        // Number formatting
        $formatted = number_format(
            $amount,
            $cfg['decimals'] ?? 0,
            $cfg['decimal_separator'] ?? '.',
            $cfg['thousand_separator'] ?? ','
        );

        // Bangla digits?
        $useBnDigits = $options['bn'] ?? $cfg['use_bn_digits'] ?? false;
        if ($useBnDigits) {
            $formatted = bn_digits($formatted);
        }

        // With currency symbol/label
        $withLabel = $options['label'] ?? false;

        if ($withLabel) {
            // Example: "১২,৫০০ টাকা" or "12,500 টাকা"
            return $formatted . ' ' . ($cfg['label'] ?? '');
        }

        $symbol   = $cfg['symbol'] ?? '৳';
        $position = $cfg['position'] ?? 'before';

        if ($position === 'after') {
            return $formatted . ' ' . $symbol;  // e.g. "12,500 ৳"
        }

        return $symbol . ' ' . $formatted;      // e.g. "৳ 12,500"
    }

if (! function_exists('banner_path')) {
    function banner_path(string $key): ?string
    {
        $banner = Banners::where('key', $key)->first();

        return $banner && $banner->value ? $banner->value : null;
    }
}


if (! function_exists('banner_url')) {
    function banner_url(string $key, ?string $default = null): ?string
    {
        $path = banner_path($key);

        if (! $path) {
            return $default;
        }

        return Storage::disk('public')->url($path);
    }
}


if (! function_exists('banner_urls')) {
    function banner_urls(array $keys): array
    {
        $paths = Banners::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        $urls = [];
        foreach ($paths as $key => $path) {
            $urls[$key] = $path
                ? Storage::disk('public')->url($path)
                : null;
        }

       
        foreach ($keys as $key) {
            if (! array_key_exists($key, $urls)) {
                $urls[$key] = null;
            }
        }

        return $urls;
    }
}
}
