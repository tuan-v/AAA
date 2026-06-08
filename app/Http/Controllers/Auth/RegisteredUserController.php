<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {

        $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'phone' => ['required', 'regex:/^(0[0-9]{9,10})$/', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
            [
                'name.required' => 'Họ tên không được để trống.',
                'name.max' => 'Họ tên tối đa 255 ký tự.',

                'email.required' => 'Email không được để trống.',
                'email.email' => 'Email không đúng định dạng.',
                'email.unique' => 'Email đã tồn tại.',

                'phone.required' => 'Số điện thoại không được để trống.',
                'phone.regex' => 'Số điện thoại không hợp lệ.',
                'phone.unique' => 'Số điện thoại đã tồn tại.',

                'password.required' => 'Mật khẩu không được để trống.',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            ]
        );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => User::STATUS_PENDING,
        ]);
        // // $user->assignRole('CompanyManager');
        // $user->save();
        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('company.create');
    }
}
