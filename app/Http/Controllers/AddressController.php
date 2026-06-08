<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Ward;

class AddressController extends Controller
{
    public function provinces()
    {
        return Province::orderBy('name')->get();
    }

    public function wards($provinceId)
    {
        return Ward::where('province_id', $provinceId)->get();
    }
}
