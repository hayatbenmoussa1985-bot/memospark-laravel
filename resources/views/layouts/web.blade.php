<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'MemoSpark' }} — MemoSpark</title>
    <meta name="description" content="{{ $metaDescription ?? 'MemoSpark helps you learn and remember with flashcards and a structured review plan.' }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $title ?? 'MemoSpark' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'MemoSpark helps you learn and remember with flashcards and a structured review plan.' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

    {{-- Navigation --}}
    <nav class="bg-[#1F3B9F] text-white sticky top-0 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('web.home') }}" class="text-xl font-bold tracking-tight">
                    MemoSpark
                </a>

                {{-- Desktop links --}}
                <div class="hidden sm:flex items-center gap-6 text-sm font-medium">
                    <a href="{{ route('web.home') }}" class="hover:text-emerald-300 transition {{ request()->routeIs('web.home') ? 'text-emerald-300' : '' }}">Home</a>
                    <a href="{{ route('web.guide') }}" class="hover:text-emerald-300 transition {{ request()->routeIs('web.guide') ? 'text-emerald-300' : '' }}">Guide</a>
                    <a href="{{ route('web.help') }}" class="hover:text-emerald-300 transition {{ request()->routeIs('web.help*') ? 'text-emerald-300' : '' }}">Help</a>
                    <a href="{{ route('web.contact') }}" class="hover:text-emerald-300 transition {{ request()->routeIs('web.contact') ? 'text-emerald-300' : '' }}">Contact</a>
                    <a href="{{ route('web.blog.index') }}" class="hover:text-emerald-300 transition {{ request()->routeIs('web.blog*') ? 'text-emerald-300' : '' }}">Blog</a>
                </div>

                {{-- CTA + mobile toggle --}}
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('user.dashboard') }}" class="hidden sm:inline-block px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-block px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition">
                            Sign In
                        </a>
                    @endauth

                    <button @click="mobileOpen = !mobileOpen" class="sm:hidden p-2 rounded-lg hover:bg-white/10 transition">
                        <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div x-show="mobileOpen" x-cloak x-transition class="sm:hidden pb-4 border-t border-white/20 pt-3 space-y-1">
                <a href="{{ route('web.home') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10">Home</a>
                <a href="{{ route('web.guide') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10">Guide</a>
                <a href="{{ route('web.help') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10">Help</a>
                <a href="{{ route('web.contact') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10">Contact</a>
                <a href="{{ route('web.blog.index') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10">Blog</a>
                @auth
                    <a href="{{ route('user.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm bg-emerald-500 hover:bg-emerald-600 mt-2">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-sm bg-emerald-500 hover:bg-emerald-600 mt-2">Sign In</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-5xl mx-auto px-4 sm:px-6 mt-4">
            <div class="bg-emerald-50 text-emerald-700 px-4 py-3 rounded-lg text-sm border border-emerald-200">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-5xl mx-auto px-4 sm:px-6 mt-4">
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm border border-red-200">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-400 mt-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm">&copy; {{ date('Y') }} MemoSpark</p>
                <div class="flex items-center gap-6 text-sm">
                    <a href="{{ route('web.help') }}" class="hover:text-white transition">Help</a>
                    <a href="{{ route('web.contact') }}" class="hover:text-white transition">Contact</a>
                    <a href="{{ route('web.privacy') }}" class="hover:text-white transition">Privacy Policy</a>
                    <a href="{{ route('web.terms') }}" class="hover:text-white transition">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
