<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Ward;

class ProvinceController extends Controller
{
    public function index()
    {
        return Province::orderBy('name')->get();
    }

    public function wards($provinceId)
    {
        return Ward::where(
            'province_id',
            $provinceId
        )
            ->orderBy('name')
            ->get();
    }
}
