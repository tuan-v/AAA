<x-auth-layout>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="w-full max-w-md space-y-8 mx-auto bg-white p-8 rounded-lg shadow-lg">

        <div>
            <h2 class="text-center text-2xl font-bold">Chào mừng đến với ASFY</h2>
        </div>

        <div class="space-y-1 mb-5">

            <form class="space-y-6" method="POST" action="{{ url()->current() }}">
                @csrf
                <input type="hidden" name="remember" value="true">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"

                        class="block w-full px-4 py-3 border rounded-lg
        @error('email')
            border-red-500
        @else
            border-gray-300
        @enderror"
                        placeholder="example@email.com">

                    @error('email')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                    @enderror
                </div>
                <!-- <div class="mb-4">
                    <label for="password" class="block text-sm font-medium">Mật khẩu</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                  transition duration-200" placeholder="Nhập mật khẩu">

                    @if ($errors->has('password'))
                    <p class="text-red-500 text-sm mt-1">{{ $errors->first('password') }}</p>
                    @endif
                </div> -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Mật khẩu
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"

                        autocomplete="current-password"
                        class="block w-full px-4 py-3 border rounded-lg
        @error('password')
            border-red-500
        @else
            border-gray-300
        @enderror"
                        placeholder="••••••••">

                    @error('password')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <button type="submit" class="w-full flex cursor-pointer justify-center py-2 px-4 border border-transparent 
                               rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                               transition duration-200 transform hover:scale-[1.01]">
                        <span>Đăng nhập</span>
                    </button>
                </div>

            </form>
        </div>
        <div class="relative mb-2">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-white px-2 text-gray-500">Hoặc kết nối với: </span>
            </div>
        </div>
        <div class="flex justify-center mb-4">
            <a href="{{ url('/login/google') }}" class="inline-flex w-full justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm 
                       text-sm font-medium bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 
                       focus:ring-blue-500 transition duration-200 transform hover:scale-[1.01]">

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

                <span class="ms-3">Tiếp tục với Google</span>
            </a>
        </div>

    </div>

</x-auth-layout>