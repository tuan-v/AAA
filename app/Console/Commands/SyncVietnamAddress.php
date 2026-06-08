<?php

namespace App\Console\Commands;

use App\Models\Province;
use App\Models\Ward;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncVietnamAddress extends Command
{
    protected $signature = 'address:sync';

    protected $description = 'Sync provinces and wards';

    public function handle()
    {
        $this->info('Đang đồng bộ...');

        $provincesResponse = Http::get(
            'https://provinces.open-api.vn/api/v2/p/'
        );

        if (!$provincesResponse->successful()) {
            $this->error('Không lấy được tỉnh');
            return Command::FAILURE;
        }

        foreach ($provincesResponse->json() as $provinceData) {

            $province = Province::updateOrCreate(
                [
                    'code' => $provinceData['code']
                ],
                [
                    'name' => $provinceData['name']
                ]
            );

            $wardsResponse = Http::get(
                'https://provinces.open-api.vn/api/v2/w/',
                [
                    'province' => $provinceData['code']
                ]
            );

            if (!$wardsResponse->successful()) {
                continue;
            }

            foreach ($wardsResponse->json() as $wardData) {

                Ward::updateOrCreate(
                    [
                        'code' => $wardData['code']
                    ],
                    [
                        'province_id' => $province->id,
                        'name' => $wardData['name']
                    ]
                );
            }

            $this->info(
                'Đã đồng bộ: ' . $province->name
            );
        }

        $this->info('Hoàn thành');

        return Command::SUCCESS;
    }
}
