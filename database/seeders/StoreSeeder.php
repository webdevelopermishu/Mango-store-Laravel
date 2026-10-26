<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Creates the six starter categories and a few SAMPLE products.
 * Every sample product is a draft and every price is a placeholder: nothing is shown
 * to customers until the owner edits it and publishes it in the admin.
 */
class StoreSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $categories = [
                ['aam', 'আম', 'Mangoes', 'আমের মৌসুমি জাত ও প্রিমিয়াম প্যাক।', 'Seasonal mango varieties and premium packs.', true],
                ['gur', 'গুড়', 'Gur (Jaggery)', 'খেজুরের রস ও আখের খাঁটি গুড়।', 'Pure date palm and cane jaggery.', true],
                ['ghee', 'ঘি', 'Ghee', 'খাঁটি ঘি বিভিন্ন প্যাক সাইজে।', 'Pure clarified butter in several pack sizes.', true],
                ['achar', 'আচার', 'Achar (Pickles)', 'আমের আচার, মিক্সড আচার ও আরও অনেক।', 'Mango pickle, mixed pickle and more.', true],
                ['kumrar-bori', 'কুমড়ার বড়ি', 'Pumpkin Bori', 'ঐতিহ্যবাহী কুমড়ার বড়ি।', 'Traditional pumpkin-based dried lentil dumplings.', true],
                ['other-products', 'অন্যান্য পণ্য', 'Other Products', 'ভবিষ্যতের নতুন পণ্যের জন্য।', 'For future product types.', false],
            ];

            $models = [];
            foreach ($categories as $i => [$slug, $nameBn, $nameEn, $descBn, $descEn, $featured]) {
                $models[$slug] = Category::updateOrCreate(['slug' => $slug], [
                    'name_bn' => $nameBn,
                    'name_en' => $nameEn,
                    'description_bn' => $descBn,
                    'description_en' => $descEn,
                    'is_published' => true,
                    'is_featured' => $featured,
                    'sort_order' => $i + 1,
                ]);
            }

            $samples = [
                [
                    'slug' => 'sample-langra-mango', 'sku' => 'SAMPLE-AAM-001',
                    'name_bn' => 'ল্যাংড়া আম (নমুনা)', 'name_en' => 'Langra Mango (sample)',
                    'unit' => 'kg', 'is_seasonal' => true, 'season_start_month' => 5, 'season_end_month' => 7,
                    'categories' => ['aam'], 'is_featured_home' => true,
                    'variants' => [['১ কেজি', '1 kg', 90000, null, 50], ['৫ কেজি প্যাক', '5 kg pack', 420000, 380000, 10]],
                ],
                [
                    'slug' => 'sample-date-palm-gur', 'sku' => 'SAMPLE-GUR-001',
                    'name_bn' => 'খেজুরের গুড় (নমুনা)', 'name_en' => 'Date Palm Gur (sample)',
                    'unit' => 'kg', 'is_seasonal' => false,
                    'categories' => ['gur'], 'is_featured_home' => true,
                    'variants' => [['৫০০ গ্রাম', '500 g', 35000, null, 40]],
                ],
                [
                    'slug' => 'sample-cow-ghee', 'sku' => 'SAMPLE-GHEE-001',
                    'name_bn' => 'গাওয়া ঘি (নমুনা)', 'name_en' => 'Cow Ghee (sample)',
                    'unit' => 'jar', 'is_seasonal' => false,
                    'categories' => ['ghee'], 'is_featured_home' => false,
                    'variants' => [['৫০০ মিলি', '500 ml', 80000, null, null]],
                ],
                [
                    'slug' => 'sample-mango-pickle', 'sku' => 'SAMPLE-ACH-001',
                    'name_bn' => 'আমের আচার (নমুনা)', 'name_en' => 'Mango Pickle (sample)',
                    'unit' => 'jar', 'is_seasonal' => false,
                    'categories' => ['achar'], 'is_featured_home' => false,
                    'variants' => [['৫০০ গ্রাম', '500 g', 30000, null, 25]],
                ],
                [
                    'slug' => 'sample-pumpkin-bori', 'sku' => 'SAMPLE-BORI-001',
                    'name_bn' => 'কুমড়ার বড়ি (নমুনা)', 'name_en' => 'Pumpkin Bori (sample)',
                    'unit' => 'pack', 'is_seasonal' => false,
                    'categories' => ['kumrar-bori'], 'is_featured_home' => false,
                    'variants' => [['২৫০ গ্রাম', '250 g', 25000, null, 60]],
                ],
            ];

            foreach ($samples as $i => $s) {
                $product = Product::updateOrCreate(['slug' => $s['slug']], [
                    'sku' => $s['sku'],
                    'name_bn' => $s['name_bn'],
                    'name_en' => $s['name_en'],
                    'status' => Product::STATUS_DRAFT,
                    'unit' => $s['unit'],
                    'is_seasonal' => $s['is_seasonal'],
                    'season_start_month' => $s['season_start_month'] ?? null,
                    'season_end_month' => $s['season_end_month'] ?? null,
                    'is_featured_home' => $s['is_featured_home'],
                    'sort_order' => $i + 1,
                ]);

                $product->categories()->sync(array_map(fn ($slug) => $models[$slug]->id, $s['categories']));

                $product->variants()->delete();
                foreach ($s['variants'] as $j => [$labelBn, $labelEn, $regular, $sale, $stock]) {
                    $product->variants()->create([
                        'label_bn' => $labelBn,
                        'label_en' => $labelEn,
                        'regular_price_paisa' => $regular,
                        'sale_price_paisa' => $sale,
                        'stock_quantity' => $stock,
                        'is_active' => true,
                        'sort_order' => $j,
                    ]);
                }
            }
        });
    }
}
