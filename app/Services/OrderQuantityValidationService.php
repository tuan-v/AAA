<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class OrderQuantityValidationService
{
    public function validate(array $items): void
    {
        $products = Product::with('unit')
            ->whereIn('id', collect($items)->pluck('product_id')->filter())
            ->get()
            ->keyBy('id');

        $errors = [];
        foreach ($items as $index => $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $product = $products->get($item['product_id'] ?? null);

            if ($product && !$product->unit?->allow_decimal && floor($quantity) !== $quantity) {
                $unitName = $product->unit?->name ?: 'đơn vị tính này';
                $errors["items.$index.quantity"] = [
                    "Sản phẩm {$product->name} dùng {$unitName}, không cho phép nhập số lượng lẻ.",
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
