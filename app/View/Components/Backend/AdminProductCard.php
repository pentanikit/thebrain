<?php

namespace App\View\Components\Backend;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Product;

class AdminProductCard extends Component
{
    /**
     * Create a new component instance.
     */
    public $product;
    public function __construct($id)
    {
        $this->product = Product::with(['images', 'specifications', 'descriptions', 'category', 'subCategory'])->latest()->paginate(20);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.backend.admin-product-card');
    }
}
