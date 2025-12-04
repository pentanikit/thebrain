<?php

namespace App\View\Components\Frontend;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Product;

class ProductCard extends Component
{
    /**
     * Create a new component instance.
     */
    public $products;
    public function __construct()
    {
        $this->products = Product::where('is_active', true)->latest()->paginate(50);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.frontend.product-card');
    }
}
