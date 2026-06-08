<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        return response()->json(
            Currency::all()
        );

        $currency = auth()->user()->company->currency;

        return response()->json([
            'currency' => $currency
        ]);
    }
}
