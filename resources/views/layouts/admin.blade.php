<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} — {{ config('app.name', 'MemoSpark') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex">

        {{-- ══════════════════════════════════════════════ --}}
        {{-- SIDEBAR (desktop: always visible, mobile: overlay) --}}
        {{-- ══════════════════════════════════════════════ --}}

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
             @click="sidebarOpen = false"
             x-cloak>
        </div>

        {{-- Sidebar panel --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-auto lg:z-auto"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            {{-- Logo --}}
            <div class="flex items-center h-16 px-6 border-b border-gray-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        M
                    </div>
                    <span class="text-lg font-bold tracking-tight">MemoSpark</span>
                </a>
                <button @click="sidebarOpen = false" class="ml-auto lg:hidden text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

                {{-- Dashboard --}}
                <x-admin.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="home">
                    Dashboard
                </x-admin.nav-link>

                {{-- Users --}}
                @if(auth()->user()->hasPermission('manage_users'))
                <x-admin.nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')" icon="users">
                    Users
                </x-admin.nav-link>
                @endif

                {{-- Decks --}}
                @if(auth()->user()->hasPermission('manage_decks'))
                <x-admin.nav-link href="{{ route('admin.decks.index') }}" :active="request()->routeIs('admin.decks.*')" icon="collection">
                    Decks
                </x-admin.nav-link>
                @endif

                {{-- Library --}}
                @if(auth()->user()->hasPermission('manage_library'))
                <x-admin.nav-link href="{{ route('admin.library.index') }}" :active="request()->routeIs('admin.library.*')" icon="library">
                    Library
                </x-admin.nav-link>
                @endif

                {{-- Subscriptions --}}
                @if(auth()->user()->hasPermission('manage_subscriptions'))
                <x-admin.nav-link href="{{ route('admin.subscriptions.index') }}" :active="request()->routeIs('admin.subscriptions.*')" icon="credit-card">
                    Subscriptions
                </x-admin.nav-link>
                @endif

                {{-- Reports --}}
                @if(auth()->user()->hasPermission('manage_reports'))
                <x-admin.nav-link href="{{ route('admin.reports.index') }}" :active="request()->routeIs('admin.reports.*')" icon="flag">
                    Reports
                    @if(($pendingReports ?? 0) > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">{{ $pendingReports }}</span>
                    @endif
                </x-admin.nav-link>
                @endif

                {{-- Blog --}}
                @if(auth()->user()->hasPermission('manage_blog'))
                <x-admin.nav-link href="{{ route('admin.blog.index') }}" :active="request()->routeIs('admin.blog.*')" icon="pencil">
                    Blog
                </x-admin.nav-link>
                @endif

                {{-- Notifications --}}
                @if(auth()->user()->hasPermission('manage_notifications'))
                <x-admin.nav-link href="{{ route('admin.notifications.index') }}" :active="request()->routeIs('admin.notifications.*')" icon="bell">
                    Notifications
                </x-admin.nav-link>
                @endif

                {{-- Analytics --}}
                @if(auth()->user()->hasPermission('view_analytics'))
                <x-admin.nav-link href="{{ route('admin.analytics.index') }}" :active="request()->routeIs('admin.analytics.*')" icon="chart">
                    Analytics
                </x-admin.nav-link>
                @endif

                <div class="pt-4 mt-4 border-t border-gray-800">
                    <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">System</p>
                </div>

                {{-- Settings --}}
                @if(auth()->user()->hasPermission('manage_settings'))
                <x-admin.nav-link href="{{ route('admin.settings.index') }}" :active="request()->routeIs('admin.settings.*')" icon="cog">
                    Settings
                </x-admin.nav-link>
                @endif

                {{-- Audit Log --}}
                @if(auth()->user()->isSuperAdmin())
                <x-admin.nav-link href="{{ route('admin.audit-log.index') }}" :active="request()->routeIs('admin.audit-log.*')" icon="clipboard">
                    Audit Log
                </x-admin.nav-link>
                @endif

                {{-- Permissions (super_admin only) --}}
                @if(auth()->user()->isSuperAdmin())
                <x-admin.nav-link href="{{ route('admin.permissions.index') }}" :active="request()->routeIs('admin.permissions.*')" icon="shield">
                    Permissions
                </x-admin.nav-link>
                @endif

            </nav>

            {{-- User card at bottom --}}
            <div class="border-t border-gray-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white text-sm font-medium">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->role->label() }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ══════════════════════════════════════════════ --}}
        {{-- MAIN CONTENT --}}
        {{-- ══════════════════════════════════════════════ --}}

        <div class="flex-1 flex flex-col min-w-0">

            {{-- Top bar --}}
            <header class="sticky top-0 z-30 flex items-center h-16 px-4 sm:px-6 bg-white border-b border-gray-200">
                {{-- Mobile menu toggle --}}
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Page title --}}
                <h1 class="text-lg font-semibold text-gray-800">
                    {{ $title ?? 'Dashboard' }}
                </h1>

                {{-- Right side --}}
                <div class="ml-auto flex items-center gap-4">
                    {{-- Back to site --}}
                    <a href="{{ url('/') }}" target="_blank" class="text-sm text-gray-500 hover:text-gray-700 hidden sm:flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        View Site
                    </a>

                    {{-- User dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                             x-cloak>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mx-4 sm:mx-6 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center justify-between">
                        <span class="text-sm">{{ session('success') }}</span>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mx-4 sm:mx-6 mt-4" x-data="{ show: true }" x-show="show">
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                        <span class="text-sm">{{ session('error') }}</span>
                        <button @click="show = false" class="text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Main content --}}
            <main class="flex-1 p-4 sm:p-6">
                {{ $slot }}
            </main>

        </div>
    </div>

</body>
</html>
