<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;


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
    });
   
    Route::get('categories', [CategoryController::class, 'index'])->name('admincategories');
    Route::post('categories/create', [CategoryController::class, 'store'])->name('admincreatecategories');
});