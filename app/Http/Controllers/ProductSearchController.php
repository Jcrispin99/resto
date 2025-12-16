<?php

namespace App\Http\Controllers;

use App\Models\ProductProduct;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    /**
     * Search products for autocomplete.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $categoryType = $request->get('type'); // 'inventory' or 'menu'

        $products = ProductProduct::with('template.category')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('sku', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%")
                        ->orWhereHas('template', function ($q3) use ($query) {
                            $q3->where('name', 'like', "%{$query}%");
                        });
                });
            })
            // Filter by category type if specified
            ->when($categoryType, function ($q) use ($categoryType) {
                $q->whereHas('template.category', function ($q2) use ($categoryType) {
                    $q2->where('type', $categoryType);
                });
            })
            ->limit(20)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->template?->name ?? 'Unknown Product',
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'sale_price' => $product->sale_price,
                ];
            });

        return response()->json($products);
    }
}
