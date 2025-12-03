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

        $products = ProductProduct::with('template')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('sku', 'like', "%{$query}%")
                        ->orWhere('barcode', 'like', "%{$query}%")
                        ->orWhereHas('template', function ($q3) use ($query) {
                            $q3->where('name', 'like', "%{$query}%");
                        });
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
