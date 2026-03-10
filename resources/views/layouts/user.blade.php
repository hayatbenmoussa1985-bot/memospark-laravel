<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() === 'ar') dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'MemoSpark' }} — {{ config('app.name', 'MemoSpark') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ mobileMenuOpen: false }">

    <div class="min-h-screen">

        {{-- ══════════════════════════════════════════════ --}}
        {{-- TOP NAVIGATION BAR --}}
        {{-- ══════════════════════════════════════════════ --}}
        <nav class="sticky top-0 z-40 bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    {{-- Logo --}}
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                            M
                        </div>
                        <span class="text-lg font-bold text-gray-900 hidden sm:inline">MemoSpark</span>
                    </a>

                    {{-- Desktop Navigation --}}
                    <div class="hidden md:flex items-center gap-1">
                        @if(auth()->user()->isParent())
                            {{-- Parent Navigation --}}
                            <x-user.nav-link href="{{ route('user.parent.dashboard') }}" :active="request()->routeIs('user.parent.dashboard')">
                                Dashboard
                            </x-user.nav-link>
                            <x-user.nav-link href="{{ route('user.parent.children') }}" :active="request()->routeIs('user.parent.children*')">
                                Children
                            </x-user.nav-link>
                            <x-user.nav-link href="{{ route('user.parent.plans.index') }}" :active="request()->routeIs('user.parent.plans*')">
                                Plans
                            </x-user.nav-link>
                            <x-user.nav-link href="{{ route('user.parent.messages') }}" :active="request()->routeIs('user.parent.messages*')">
                                Messages
                            </x-user.nav-link>
                        @else
                            {{-- Learner/Child Navigation --}}
                            <x-user.nav-link href="{{ route('user.dashboard') }}" :active="request()->routeIs('user.dashboard')">
                                Dashboard
                            </x-user.nav-link>
                            <x-user.nav-link href="{{ route('user.decks.index') }}" :active="request()->routeIs('user.decks*')">
                                My Decks
                            </x-user.nav-link>
                            <x-user.nav-link href="{{ route('user.library.index') }}" :active="request()->routeIs('user.library*')">
                                Library
                            </x-user.nav-link>
                        @endif
                    </div>

                    {{-- Right side --}}
                    <div class="flex items-center gap-3">
                        {{-- Due cards indicator --}}
                        @if(isset($dueCardsCount) && $dueCardsCount > 0)
                            <a href="{{ route('user.study.due') }}" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-full text-sm font-medium hover:bg-emerald-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $dueCardsCount }} due
                            </a>
                        @endif

                        {{-- User dropdown --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="hidden sm:inline text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50"
                                 x-cloak>
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->role->label() }}</p>
                                </div>
                                <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-emerald-600 hover:bg-gray-50">Admin Panel</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Log Out</button>
                                </form>
                            </div>
                        </div>

                        {{-- Mobile menu button --}}
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" x-cloak/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Navigation --}}
            <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-gray-200 bg-white" x-cloak>
                <div class="px-4 py-3 space-y-1">
                    @if(auth()->user()->isParent())
                        <a href="{{ route('user.parent.dashboard') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('user.parent.dashboard') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">Dashboard</a>
                        <a href="{{ route('user.parent.children') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('user.parent.children*') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">Children</a>
                        <a href="{{ route('user.parent.plans.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('user.parent.plans*') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">Plans</a>
                        <a href="{{ route('user.parent.messages') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('user.parent.messages*') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">Messages</a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('user.dashboard') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">Dashboard</a>
                        <a href="{{ route('user.decks.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('user.decks*') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">My Decks</a>
                        <a href="{{ route('user.library.index') }}" class="block px-3 py-2 text-sm rounded-lg {{ request()->routeIs('user.library*') ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">Library</a>
                    @endif
                </div>
            </div>
        </nav>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center justify-between">
                    <span class="text-sm">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" x-data="{ show: true }" x-show="show">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center justify-between">
                    <span class="text-sm">{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Main Content --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot }}
        </main>

    </div>

</body>
</html>
