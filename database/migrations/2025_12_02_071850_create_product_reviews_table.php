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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->unsignedTinyInteger('rating');      // 1–5
            $table->string('title')->nullable();
            $table->text('body')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])
                  ->default('pending');

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
