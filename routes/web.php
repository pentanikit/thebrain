<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BannersController;
use App\Http\Controllers\ProductListingController;
use App\Http\Controllers\Frontend\CartController;

//Frontend Routes

Route::prefix('/')->group(function(){
    Route::get('/', [PageController::class, 'home'])->name('homeRoute');
    
});


//Backend Routes
Route::prefix('admin')->group(function(){
    Route::get('/', [PageController::class, 'dashboard'])->name('admindashboard');

    Route::prefix('products')->group(function(){
        Route::get('/', [ProductController::class, 'index'])->name('adminproducts');
        Route::get('add', [ProductController::class, 'create'])->name('addproducts');
        Route::post('create', [ProductController::class, 'store'])->name('createproduct');
        Route::get('edit/{product}', [ProductController::class, 'edit'])->name('editproduct');
        Route::post('update/{product}', [ProductController::class, 'update'])->name('updateproduct');
    });

    Route::prefix('categories')->group(function(){
        Route::get('/', [CategoryController::class, 'index'])->name('admincategories');
        Route::post('create', [CategoryController::class, 'store'])->name('admincreatecategories');
        Route::delete('delete/{category}', [CategoryController::class, 'destroy'])->name('deletecategory');
        Route::get('search', [CategoryController::class, 'search'])->name('categorysearch');
        
        Route::get('/{parent}/children', [CategoryController::class, 'children'])->name('categorieschildren');

    });

    Route::get('banners', [BannersController::class, 'show'])->name('banners');
    Route::post('upload-banners', [BannersController::class, 'update'])->name('bannerupload');
   

});



Route::prefix('cart')->name('cart.')->group(function () {
    // full cart page
    Route::get('/', [CartController::class, 'index'])->name('index');

    // mini-cart partial (optional, if you want AJAX reload or include)
    Route::get('/mini', [CartController::class, 'mini'])->name('mini');

    // add to cart
    Route::post('/add/{product}', [CartController::class, 'add'])->name('add');

    // update quantity
    Route::post('/update/{item}', [CartController::class, 'update'])->name('update');

    // remove one item
    Route::delete('/remove/{item}', [CartController::class, 'remove'])->name('remove');

    // clear cart
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
});

// Main all-products listing
Route::get('/products', [ProductListingController::class, 'index'])
    ->name('allproducts');

// Category-wise listing: /category/led-tv, /category/32-inch-tv etc.
Route::get('/category/{slug}', [ProductListingController::class, 'category'])
    ->name('productfilter');