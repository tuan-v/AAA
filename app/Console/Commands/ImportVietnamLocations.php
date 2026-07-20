<?php

namespace App\Console\Commands;

use App\Models\Province;
use App\Models\Ward;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ImportVietnamLocations extends Command
{
    protected $signature = 'locations:import';

    protected $description = 'Đồng bộ tỉnh/thành phố và phường/xã Việt Nam từ Province Open API v2';

    public function handle(): int
    {
        $this->info('Đang tải dữ liệu đơn vị hành chính Việt Nam...');

        $response = Http::acceptJson()
            ->timeout(60)
            ->retry(3, 1000)
            ->get('https://provinces.open-api.vn/api/v2/', ['depth' => 2]);

        if ($response->failed()) {
            $this->error('API trả về lỗi HTTP '.$response->status().'.');
            return self::FAILURE;
        }

        $payload = $response->json();
        if (!is_array($payload) || $payload === []) {
            $this->error('Dữ liệu API không đúng định dạng hoặc đang trống.');
            return self::FAILURE;
        }

        $provinceCount = 0;
        $wardCount = 0;

        try {
            DB::transaction(function () use ($payload, &$provinceCount, &$wardCount) {
                foreach ($payload as $provinceData) {
                    if (!isset($provinceData['code'], $provinceData['name']) || !is_array($provinceData['wards'] ?? null)) {
                        throw new RuntimeException('Một tỉnh/thành trong dữ liệu API thiếu code, name hoặc wards.');
                    }

                    $province = Province::updateOrCreate(
                        ['code' => (string) $provinceData['code']],
                        ['name' => trim((string) $provinceData['name'])]
                    );
                    $provinceCount++;

                    foreach ($provinceData['wards'] as $wardData) {
                        if (!isset($wardData['code'], $wardData['name'])) {
                            throw new RuntimeException('Một phường/xã trong dữ liệu API thiếu code hoặc name.');
                        }

                        Ward::updateOrCreate(
                            ['code' => (string) $wardData['code']],
                            [
                                'province_id' => $province->id,
                                'name' => trim((string) $wardData['name']),
                            ]
                        );
                        $wardCount++;
                    }
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Import thất bại, toàn bộ thay đổi đã rollback: '.$exception->getMessage());
            return self::FAILURE;
        }

        $this->info("Đã đồng bộ {$provinceCount} tỉnh/thành và {$wardCount} phường/xã.");
        return self::SUCCESS;
    }
}
