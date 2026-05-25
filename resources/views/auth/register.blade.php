<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Đăng ký tài khoản</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="https://asfy.vn/resource/asfy-images/asfy-logo.svg" type="image/x-icon">
    <!-- Scripts -->
    @vite(['resources/css/main.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 overflow-hidden sm:rounded-lg">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="w-full max-w-md space-y-8 mx-auto">
                <div class="w-full max-w-md">
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Đăng ký</h2>
                        <p class="text-sm text-gray-600">
                            Đã có tài khoản?
                            <a href="{{ route('login') }}"
                                class="font-semibold text-blue-600 hover:text-blue-500 transition-colors duration-200">
                                Đăng nhập ngay
                            </a>
                        </p>
                    </div>

                    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl border border-gray-100">
                        <form class="space-y-6" method="POST" action="{{ url()->current() }}">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Tên</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name"
                                    required class="block w-full px-4 py-3 border border-gray-300 rounded-lg
                                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                                      transition-all duration-200 hover:border-gray-400"
                                    placeholder="Tên của bạn">

                                @if ($errors->has('name'))
                                <p class="text-red-500 text-sm mt-1">{{ $errors->first('name') }}</p>
                                @endif
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    autocomplete="username" required class="block w-full px-4 py-3 border border-gray-300 rounded-lg
                                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                                      transition-all duration-200 hover:border-gray-400"
                                    placeholder="example@email.com">

                                @if ($errors->has('email'))
                                <p class="text-red-500 text-sm mt-1">{{ $errors->first('email') }}</p>
                                @endif
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Số điện
                                    thoại</label>
                                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" autocomplete="tel"
                                    required class="block w-full px-4 py-3 border border-gray-300 rounded-lg
                                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                                      transition-all duration-200 hover:border-gray-400"
                                    placeholder="0123456789">

                                @if ($errors->has('phone'))
                                <p class="text-red-500 text-sm mt-1">{{ $errors->first('phone') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Mật
                                    khẩu</label>
                                <input id="password" name="password" type="password" required
                                    autocomplete="current-password" class="block w-full px-4 py-3 border border-gray-300 rounded-lg
                                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                                      transition-all duration-200 hover:border-gray-400"
                                    placeholder="••••••••">

                                @if ($errors->has('password'))
                                <p class="text-red-500 text-sm mt-1">{{ $errors->first('password') }}</p>
                                @endif
                            </div>
                            <div>
                                <label for="password_confirmation"
                                    class="block text-sm font-semibold text-gray-700 mb-2">Xác nhận mật
                                    khẩu</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required
                                    autocomplete="new-password" class="block w-full px-4 py-3 border border-gray-300 rounded-lg
                                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                                      transition-all duration-200 hover:border-gray-400"
                                    placeholder="••••••••">

                                @if ($errors->has('password_confirmation'))
                                <p class="text-red-500 text-sm mt-1">{{ $errors->first('password_confirmation') }}</p>
                                @endif
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full flex justify-center items-center py-3.5 px-4 
                                                   rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 
                                                   hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl
                                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                                                   transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99]">
                                    <span>Đăng ký</span>
                                </button>
                            </div>
                        </form>

                        <div class="relative py-2">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="bg-white px-2 text-gray-500">Hoặc kết nối với</span>
                            </div>
                        </div>

                        <div>
                            <a href="{{ route('login.google') }}"
                                class="inline-flex w-full justify-center items-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm 
                                               text-sm font-medium bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 
                                               focus:ring-blue-500 transition duration-200 transform hover:scale-[1.02]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24">
                                    <path
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                        fill="#4285F4" />
                                    <path
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                        fill="#34A853" />
                                    <path
                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                        fill="#FBBC05" />
                                    <path
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                        fill="#EA4335" />
                                </svg>
                                <span class="ml-3">Tiếp tục với Google</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>