<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CurrencyService;
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currencyService = app(CurrencyService::class);
        $currency = $currencyService->getCompanyCurrency(auth()->user()->company);
        return [

            'id' => $this->id,

            'name' => $this->name,

            'sku' => $this->sku,

            'purchase_price' => $this->purchase_price,

            'sell_price' => $this->sell_price,

            'quantity' => $this->quantity,

            'image' => $this->image ? asset("storage/{$this->image}") : null,

            'category' => $this->category,

            'unit' => $this->unit,

            'purchase_price_vnd' => $this->convertByCurrency(

                $this->purchase_price,

                $currency

            ),

            'sell_price_vnd' => $this->convertByCurrency(

                $this->sell_price,

                $currency

            ),

        ];
    }
}
