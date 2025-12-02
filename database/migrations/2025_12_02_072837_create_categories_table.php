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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Self-referencing parent for subcategories / child categories
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('name');                 // e.g. "Men", "Caps", "Panjabis", "Jeans"
            $table->string('slug')->unique();      // e.g. "men", "caps", "men-panjabis"
            $table->unsignedTinyInteger('level')   // 1 = main, 2 = sub, 3 = child etc.
                ->default(1);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('thumbnail')->nullable();

            $table->timestamps();

            $table->index(['parent_id', 'level']);
            $table->index('is_active');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
