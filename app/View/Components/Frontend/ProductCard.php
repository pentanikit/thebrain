<?php

namespace App\View\Components\Frontend;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Product;
use App\Models\Category;

class ProductCard extends Component
{
    /**
     * Create a new component instance.
     */
    public $products;
    public $type;
    public function __construct($type)
    {
        $categoryType = Category::where('name', $type)->pluck('id')->toArray();
        $this->products = Product::whereIn('sub_category_id', $categoryType)->orWhereIn('category_id', $categoryType)->where('is_active', true)->latest()->paginate(16);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.frontend.product-card');
    }
}
