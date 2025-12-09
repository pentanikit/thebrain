<?php

namespace App\Console\Commands;

use App\Models\OldProduct;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\ProductDescription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateLegacyProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan products:migrate-legacy --chunk=100
     */
    protected $signature = 'products:migrate-legacy {--chunk=100}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate products from legacy products table to new normalized tables';

    /**
     * Default category id to use if mapping fails.
     * 👉 এখানে তোমার "Uncategorized" বা ডিফল্ট ক্যাটাগরির id বসিয়ে দাও
     */
    protected int $fallbackCategoryId = 1;

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');

        $this->info("Starting legacy product migration (chunk size: {$chunkSize})");

        $total = OldProduct::count();
        $this->info("Total legacy products found: {$total}");

        $migratedCount = 0;

        OldProduct::chunk($chunkSize, function ($oldProducts) use (&$migratedCount) {
            foreach ($oldProducts as $old) {
                DB::beginTransaction();

                try {
                    // 1) Category resolve
                    $categoryId = $this->resolveCategoryId($old->category);

                    // 2) New product create
                    $product = Product::create([
                        'category_id'      => $categoryId,
                        'sub_category_id'  => null,
                        'child_category_id'=> null,

                        'name'             => $old->name,
                        'slug'             => $this->generateSlug($old),
                        'sku'              => $this->generateSku($old),

                        'price'            => $this->convertMoney($old->price),
                        'old_price'        => $old->offer_price
                                                ? $this->convertMoney($old->price)
                                                : null,
                        'offer_price'      => $this->convertMoney($old->offer_price),

                        'stock_quantity'   => $this->convertStockQuantity($old->in_stock),
                        'stock_status'     => $this->convertStockStatus($old),

                        'thumbnail'        => $this->extractThumbnail($old),

                        'is_active'        => $old->status === 'active',
                    ]);

                    // 3) Images table
                    $this->createImages($product, $old);

                    // 4) Specifications (color, size, raw spec)
                    $this->createSpecifications($product, $old);

                    // 5) Description (optional: পুরনো specification কে description হিসাবে রাখলাম)
                    $this->createDescription($product, $old);

                    DB::commit();

                    $migratedCount++;
                    $this->line("✔ Migrated legacy product #{$old->id} → new #{$product->id}");
                } catch (\Throwable $e) {
                    DB::rollBack();
                    $this->error("✖ Failed to migrate legacy product #{$old->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Migration finished. Successfully migrated: {$migratedCount} products.");

        return Command::SUCCESS;
    }

    /**
     * পুরনো category string থেকে নতুন category_id বের করা
     */
    protected function resolveCategoryId(?string $category): int
    {
        if (!$category) {
            return $this->fallbackCategoryId;
        }

        $categoryId = Category::where('slug', $category)
            ->orWhere('name', $category)
            ->value('id');

        return $categoryId ?? $this->fallbackCategoryId;
    }

    /**
     * Money decimal → integer
     * এখানে তুমি চাইলে ×100 করতে পারো, যদি পয়সা/কয়েন হিসেবে রাখো
     */
    protected function convertMoney($value): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) round((float) $value); // চাইলে (float)$value * 100 করে নিতে পারো
    }

    /**
     * stock_quantity সেট করা
     */
    protected function convertStockQuantity($inStock): int
    {
        // যদি আগে 0/1 হিসেবে রেখে থাকো, তাহলে 1 মানে 1 পিস ধরে নিচ্ছি
        if ($inStock === null) {
            return 0;
        }

        return max(0, (int) $inStock);
    }

    /**
     * stock_status নির্ধারণ
     */
    protected function convertStockStatus(OldProduct $old): string
    {
        if ((int) $old->in_stock > 0 && $old->status === 'active') {
            return 'in_stock';
        }

        return 'out_of_stock';
    }

    /**
     * slug generate + uniqueness ensure
     */
    protected function generateSlug(OldProduct $old): string
    {
        $base = Str::slug($old->name);

        if ($base === '') {
            $base = 'product-' . $old->id;
        }

        $slug = $base;
        $i = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $old->id . '-' . $i;
            $i++;
        }

        return $slug;
    }

    /**
     * legacy product থেকে sku set করা
     */
    protected function generateSku(OldProduct $old): ?string
    {
        // পুরনো table এ sku ছিল না, তাই simple unique sku বানালাম
        return 'TB-' . $old->id;
    }

    /**
     * JSON field safe decode
     */
    protected function decodeJsonField($value): array
    {
        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * Thumbnail বের করা (images JSON এর প্রথম image)
     */
    protected function extractThumbnail(OldProduct $old): ?string
    {
        $images = $this->decodeJsonField($old->images);

        return $images[0] ?? null;
    }

    /**
     * product_images টেবিলে ইমেজ ইনসার্ট
     */
    protected function createImages(Product $product, OldProduct $old): void
    {
        $images = $this->decodeJsonField($old->images);

        if (empty($images)) {
            return;
        }

        foreach ($images as $path) {
            if (!$path) {
                continue;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'alt_text'   => $product->name,
            ]);
        }
    }

    /**
     * product_specifications টেবিলে color, size + raw spec json হিসেবে রাখা
     */
    protected function createSpecifications(Product $product, OldProduct $old): void
    {
        $colors = $this->decodeJsonField($old->color);
        $sizes  = $this->decodeJsonField($old->size);

        $specData = [
            'colors' => $colors,
            'sizes'  => $sizes,
        ];

        if (!empty($old->specification)) {
            $specData['raw_specification'] = $old->specification;
        }

        // যদি কিছুই না থাকে, তাহলে row তৈরি করার দরকার নেই
        if (
            empty($specData['colors']) &&
            empty($specData['sizes']) &&
            !isset($specData['raw_specification'])
        ) {
            return;
        }

        ProductSpecification::create([
            'product_id' => $product->id,
            'value'      => $specData,
        ]);
    }

    /**
     * product_descriptions টেবিলে description (body) রাখা
     * এখানে আমি পুরনো specification ফিল্ডকেই description ধরে নিলাম
     */
    protected function createDescription(Product $product, OldProduct $old): void
    {
        if (empty($old->specification)) {
            return;
        }

        ProductDescription::create([
            'product_id' => $product->id,
            'body'       => $old->specification,
            'sort_order' => 0,
        ]);
    }
}
