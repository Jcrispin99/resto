<?php

namespace Database\Seeders;

use App\Models\ProductProduct;
use App\Models\ProductTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 20 Products
        // We create templates, and for each template we ensure a variant exists
        ProductTemplate::factory()
            ->count(20)
            ->create()
            ->each(function ($template) {
                // Create the default variant for this template
                ProductProduct::factory()->create([
                    'template_id' => $template->id,
                    'sku' => $template->internal_reference ?? 'SKU-' . $template->id,
                    'barcode' => $template->barcode,
                    'sale_price' => null, // Inherit from template
                ]);
            });
    }
}
