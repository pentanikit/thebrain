<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BannersController;


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

Route::get('/products', [ProductController::class, 'filter'])
    ->name('products'); // main listing + search + filter

Route::get('/category/{category:slug}', [ProductController::class, 'category'])
    ->name('category'); // category wise listing