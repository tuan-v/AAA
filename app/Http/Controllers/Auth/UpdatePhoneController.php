<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UpdatePhoneController extends Controller
{
    public function index()
    {
        return view('auth.update-phone');
    }

    public function update(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:15|unique:users,phone,' . auth()->id(),
        ]);

        auth()->user()->update([
            'phone' => $request->input('phone')
        ]);
        return redirect()->intended('/')
            ->with('status', 'Cập nhật số điện thoại thành công.');
    }
}
