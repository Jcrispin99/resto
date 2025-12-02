<?php

namespace App\Services;

use App\Models\ProductProduct;
use App\Models\ProductTemplate;
use Illuminate\Support\Facades\DB;

class ProductVariantService
{
    public function generateVariants(ProductTemplate $template)
    {
        $attributeLines = $template->attributeLines()->with('values')->get();

        // If no attributes, ensure a single default variant exists (or handle as needed)
        if ($attributeLines->isEmpty()) {
            $this->createOrUpdateVariant($template, []);
            return;
        }

        // Prepare arrays of values for Cartesian product
        $attributesData = [];
        foreach ($attributeLines as $line) {
            $attributesData[] = $line->values->pluck('id')->toArray();
        }

        // Generate combinations
        $combinations = $this->cartesianProduct($attributesData);

        $validVariantIds = [];

        foreach ($combinations as $combination) {
            // $combination is an array of value IDs, one per attribute line
            $variant = $this->createOrUpdateVariant($template, $combination);
            $validVariantIds[] = $variant->id;
        }

        // Deactivate or delete variants that are no longer valid
        // (i.e., variants that have attribute combinations not present in the current configuration)
        // Note: Logic here needs to be careful not to delete variants that just have *different* attributes if we support that.
        // But for a strict template-variant system, usually all variants must match the template configuration.
        
        // Simple approach: Deactivate variants not in the valid list
        $template->products()->whereNotIn('id', $validVariantIds)->update(['is_active' => false]);
    }

    protected function createOrUpdateVariant(ProductTemplate $template, array $valueIds)
    {
        // Find existing variant with these exact attribute values
        // This is the tricky part. We need to query ProductProduct where it has exactly these attribute_value_product entries.
        
        $variant = $this->findVariantByAttributes($template, $valueIds);

        if (!$variant) {
            $variant = ProductProduct::create([
                'template_id' => $template->id,
                'sku' => $this->generateSku($template, $valueIds),
                'sale_price' => $template->sale_price, // Default to template price
                'is_active' => true,
            ]);

            // Attach values
            if (!empty($valueIds)) {
                $variant->attributeValues()->attach($valueIds);
            }
        } else {
            // Reactivate if needed
            if (!$variant->is_active) {
                $variant->update(['is_active' => true]);
            }
        }

        return $variant;
    }

    protected function findVariantByAttributes(ProductTemplate $template, array $valueIds)
    {
        if (empty($valueIds)) {
            return $template->products()->doesntHave('attributeValues')->first();
        }

        // Query for variant that has ALL these values and ONLY these values
        // This can be complex in Eloquent.
        // A common approach is to filter by count and intersection.
        
        $count = count($valueIds);
        
        return ProductProduct::where('template_id', $template->id)
            ->whereHas('attributeValues', function ($q) use ($valueIds) {
                $q->whereIn('product_attribute_values.id', $valueIds);
            }, '=', $count)
            ->whereDoesntHave('attributeValues', function ($q) use ($valueIds) {
                $q->whereNotIn('product_attribute_values.id', $valueIds);
            })
            ->first();
    }

    protected function cartesianProduct($input)
    {
        $result = [[]];

        foreach ($input as $key => $values) {
            $append = [];
            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }
            $result = $append;
        }

        return $result;
    }

    protected function generateSku(ProductTemplate $template, array $valueIds)
    {
        // Simple SKU generation logic
        $sku = $template->internal_reference ?? ('PROD-' . $template->id);
        if (!empty($valueIds)) {
            $sku .= '-' . implode('-', $valueIds);
        }
        return $sku . '-' . uniqid(); // Ensure uniqueness for now
    }
}
