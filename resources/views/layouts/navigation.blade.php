<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700"
    style="box-shadow: rgba(0, 0, 0, 0.24) 0px 1px 5px;">
    <!-- Primary Navigation Menu -->
    <div class="max-w-10xl mx-auto px-3 sm:px-5 lg:px-7">
        <div class="flex justify-between items-center h-16">

            <!-- Left: Logo + Menu icon -->
            <div class="flex items-center gap-5">
                <div class="hidden sm:flex cursor-pointer" @click="toggle()">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#8e94a3">
                        <path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z" />
                    </svg>
                </div>

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>
            </div>

            <!-- Middle: Search input -->
            <div class="flex-1 max-w-md mx-4 hidden sm:block">
                <form action="" method="GET">
                    <div class="relative">
                        <input type="text" name="q" placeholder="Tìm kiếm trên website..."
                            class="w-full pl-10 rounded-full border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm px-4 py-2 focus:outline-none " />
                        <button type="submit"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Avatar + Dropdown -->
            <div class="hidden sm:flex items-center sm:ms-6 gap-3">
                <!-- Avatar -->
                <div class="w-9 h-9 rounded-full overflow-hidden border border-gray-300 dark:border-gray-700">

                    @if (Auth::user()->thumbnail)
                    <img src="{{ asset('storage/' . Auth::user()->thumbnail) }}" alt="{{ Auth::user()->name }}"
                        class="devc__admin__avatar-img">
                    @else
                    <img src="{{ Auth::user()->avatar ?? 'https://img.freepik.com/vector-cao-cap/mot-nguoi-mac-ao-so-mi-xanh-co-dong-chu-ten-cua-nguoi-do_1029948-7040.jpg?semt=ais_hybrid&w=740&q=80' }}"
                        alt="{{ Auth::user()->name }}" class="w-full h-full object-cover" />
                    @endif
                </div>

                <!-- Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link href="/lw-admin/user/{{ Auth::user()->id }}/edit">
                            Tài khoản
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="GET" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Đăng xuất
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="toggle()"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Menu (mobile) -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full overflow-hidden border border-gray-300 dark:border-gray-700">
                    <img src="{{ Auth::user()->avatar ?? 'https://img.freepik.com/vector-cao-cap/mot-nguoi-mac-ao-so-mi-xanh-co-dong-chu-ten-cua-nguoi-do_1029948-7040.jpg?semt=ais_hybrid&w=740&q=80' }}"
                        alt="{{ Auth::user()->name }}" class="w-full h-full object-cover" />
                </div>
                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="/lw-admin/user/{{ Auth::user()->id }}/edit">
                    Tài khoản
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Đăng xuất
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>