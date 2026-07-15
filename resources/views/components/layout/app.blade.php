<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Duty Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div class="min-h-full" x-data="{ mobileOpen: false, showLogoutConfirm: false }">
    <x-flash-messages />

    <nav class="border-b border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <div class="flex">
                    <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('dashboard') }}" aria-current="{{ request()->is('/') ? 'page' : '' }}" class="{{ request()->is('/') ? 'inline-flex items-center border-b-2 border-indigo-600 px-1 pt-1 text-sm font-medium text-gray-900 dark:border-indigo-500 dark:text-white' : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-white/20 dark:hover:text-gray-200' }}">Dashboard</a>
                        <a href="{{ route('members.index') }}" aria-current="{{ request()->is('members*') ? 'page' : '' }}" class="{{ request()->is('members*') ? 'inline-flex items-center border-b-2 border-indigo-600 px-1 pt-1 text-sm font-medium text-gray-900 dark:border-indigo-500 dark:text-white' : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-white/20 dark:hover:text-gray-200' }}">Members</a>
                        <a href="{{ route('vehicles.index') }}" aria-current="{{ request()->is('vehicles*') ? 'page' : '' }}" class="{{ request()->is('vehicles*') ? 'inline-flex items-center border-b-2 border-indigo-600 px-1 pt-1 text-sm font-medium text-gray-900 dark:border-indigo-500 dark:text-white' : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-white/20 dark:hover:text-gray-200' }}">Vehicles</a>
                        <a href="{{ route('duties.index') }}" aria-current="{{ request()->is('duties*') ? 'page' : '' }}" class="{{ request()->is('duties*') ? 'inline-flex items-center border-b-2 border-indigo-600 px-1 pt-1 text-sm font-medium text-gray-900 dark:border-indigo-500 dark:text-white' : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-white/20 dark:hover:text-gray-200' }}">Duties</a>
                        <a href="{{ route('users.index') }}" aria-current="{{ request()->is('users*') ? 'page' : '' }}" class="{{ request()->is('users*') ? 'inline-flex items-center border-b-2 border-indigo-600 px-1 pt-1 text-sm font-medium text-gray-900 dark:border-indigo-500 dark:text-white' : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-white/20 dark:hover:text-gray-200' }}">Users</a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <button type="button" class="relative rounded-full p-1 text-gray-400 hover:text-gray-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-600 dark:text-gray-400 dark:hover:text-white dark:focus:outline-indigo-500">
                        <span class="absolute -inset-1.5"></span>
                        <span class="sr-only">View notifications</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                            <path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <!-- Profile dropdown -->
                    <div class="relative ml-3" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="relative flex max-w-xs items-center rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:focus-visible:outline-indigo-500" x-bind:aria-expanded="open">
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">Open user menu</span>
                            <img src="{{ Auth::user()->gravatar }}" alt="" class="size-8 rounded-full outline -outline-offset-1 outline-black/5 dark:outline-white/10" />
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg outline outline-black/5 dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-hidden dark:text-gray-300 dark:hover:bg-white/5 dark:focus:bg-white/5">Your profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-hidden dark:text-gray-300 dark:hover:bg-white/5 dark:focus:bg-white/5">Settings</a>
                            <a href="#" @click.prevent="open = false; showLogoutConfirm = true" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-hidden dark:text-gray-300 dark:hover:bg-white/5 dark:focus:bg-white/5">Sign out</a>
                        </div>
                    </div>
                </div>
                <div class="-mr-2 flex items-center sm:hidden">
                    <!-- Mobile menu button -->
                    <button @click="mobileOpen = !mobileOpen" type="button" class="relative inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-600 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white dark:focus:outline-indigo-500" x-bind:aria-expanded="mobileOpen">
                        <span class="absolute -inset-0.5"></span>
                        <span class="sr-only">Open main menu</span>
                        <svg x-show="!mobileOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                            <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg x-show="mobileOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileOpen" class="block sm:hidden">
            <div class="space-y-1 pt-2 pb-3">
                <a href="{{ url('/') }}" aria-current="{{ request()->is('/') ? 'page' : '' }}" class="{{ request()->is('/') ? 'block border-l-4 border-indigo-600 bg-indigo-50 py-2 pr-4 pl-3 text-base font-medium text-indigo-700 dark:border-indigo-500 dark:bg-indigo-600/10 dark:text-indigo-300' : 'block border-l-4 border-transparent py-2 pr-4 pl-3 text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200' }}">Dashboard</a>
                <a href="{{ route('members.index') }}" aria-current="{{ request()->is('members*') ? 'page' : '' }}" class="{{ request()->is('members*') ? 'block border-l-4 border-indigo-600 bg-indigo-50 py-2 pr-4 pl-3 text-base font-medium text-indigo-700 dark:border-indigo-500 dark:bg-indigo-600/10 dark:text-indigo-300' : 'block border-l-4 border-transparent py-2 pr-4 pl-3 text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200' }}">Members</a>
                <a href="{{ route('vehicles.index') }}" aria-current="{{ request()->is('vehicles*') ? 'page' : '' }}" class="{{ request()->is('vehicles*') ? 'block border-l-4 border-indigo-600 bg-indigo-50 py-2 pr-4 pl-3 text-base font-medium text-indigo-700 dark:border-indigo-500 dark:bg-indigo-600/10 dark:text-indigo-300' : 'block border-l-4 border-transparent py-2 pr-4 pl-3 text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200' }}">Vehicles</a>
                <a href="{{ route('duties.index') }}" aria-current="{{ request()->is('duties*') ? 'page' : '' }}" class="{{ request()->is('duties*') ? 'block border-l-4 border-indigo-600 bg-indigo-50 py-2 pr-4 pl-3 text-base font-medium text-indigo-700 dark:border-indigo-500 dark:bg-indigo-600/10 dark:text-indigo-300' : 'block border-l-4 border-transparent py-2 pr-4 pl-3 text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200' }}">Duties</a>
                <a href="{{ route('users.index') }}" aria-current="{{ request()->is('users*') ? 'page' : '' }}" class="{{ request()->is('users*') ? 'block border-l-4 border-indigo-600 bg-indigo-50 py-2 pr-4 pl-3 text-base font-medium text-indigo-700 dark:border-indigo-500 dark:bg-indigo-600/10 dark:text-indigo-300' : 'block border-l-4 border-transparent py-2 pr-4 pl-3 text-base font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200' }}">Users</a>
            </div>
            <div class="border-t border-gray-200 pt-4 pb-3 dark:border-gray-700">
                <div class="flex items-center px-4">
                    <div class="shrink-0">
                        <img src="{{ Auth::user()->gravatar }}" alt="" class="size-10 rounded-full outline -outline-offset-1 outline-black/5 dark:outline-white/10" />
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800 dark:text-white">Tom Cook</div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">tom@example.com</div>
                    </div>
                    <button type="button" class="relative ml-auto shrink-0 rounded-full p-1 text-gray-400 hover:text-gray-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-600 dark:text-gray-400 dark:hover:text-white dark:focus:outline-indigo-500">
                        <span class="absolute -inset-1.5"></span>
                        <span class="sr-only">View notifications</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                            <path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <div class="mt-3 space-y-1">
                    <a href="#" class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200">Your profile</a>
                    <a href="#" class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200">Settings</a>
                    <a href="#" @click.prevent="mobileOpen = false; showLogoutConfirm = true" class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200">Sign out</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </main>

    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="hidden">
        @csrf
    </form>

    <div x-show="showLogoutConfirm" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="showLogoutConfirm = false"></div>
        <div class="relative z-10 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm Sign Out</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Are you sure you want to sign out?</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="showLogoutConfirm = false" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-600">Cancel</button>
                <button type="button" @click="document.getElementById('logout-form').submit()" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign out</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
