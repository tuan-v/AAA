<?php

namespace App\Http\Controllers;

use App\Models\TheLoai;

class TheLoaiController extends Controller
{
    public function index()
    {
        return response()->json(
            TheLoai::all()
        );
    }
}
