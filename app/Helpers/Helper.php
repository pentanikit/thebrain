<?php

use App\Models\Category;

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
