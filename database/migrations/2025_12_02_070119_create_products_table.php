<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Category relations
            $table->foreignId('category_id')           
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->foreignId('sub_category_id')      
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->foreignId('child_category_id')     
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();

            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('old_price')->nullable();
            $table->unsignedBigInteger('offer_price')->nullable();

            $table->unsignedInteger('stock_quantity')->default(0);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'preorder'])
                ->default('in_stock');

            $table->string('thumbnail')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['stock_status', 'is_active']);
            $table->index('price');

            // Useful for filtering by category
            $table->index(['category_id', 'sub_category_id', 'child_category_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
