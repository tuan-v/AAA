<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'VND', 'name' => 'Việt Nam Đồng', 'symbol' => '₫', 'exchange_rate' => 1],
            ['code' => 'USD', 'name' => 'Đô la Mỹ', 'symbol' => '$', 'exchange_rate' => 26100],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 30300],
            ['code' => 'JPY', 'name' => 'Yên Nhật', 'symbol' => '¥', 'exchange_rate' => 175],
            ['code' => 'CNY', 'name' => 'Nhân dân tệ', 'symbol' => '¥', 'exchange_rate' => 3600],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency + ['is_active' => true]
            );
        }
    }
}
