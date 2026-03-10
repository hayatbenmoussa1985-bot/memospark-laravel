<x-web-layout title="Home" metaDescription="MemoSpark helps you learn and remember with flashcards and a structured review plan.">

    {{-- Hero --}}
    <section class="text-center py-12 sm:py-20">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">
            Memo<span class="text-emerald-600">Spark</span>
        </h1>
        <p class="text-lg sm:text-xl text-slate-600 max-w-xl mx-auto mb-8">
            Learn and remember with flashcards and a structured review plan.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('web.help.get-started') }}"
               class="px-8 py-3 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                Get Started
            </a>
            <a href="{{ route('web.help') }}"
               class="px-8 py-3 text-sm font-semibold text-slate-700 border border-slate-300 rounded-xl hover:bg-white transition">
                Help &amp; Support
            </a>
        </div>
    </section>

    {{-- Features grid --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">

        {{-- Features card --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mb-3">Features</h2>
            <ul class="space-y-2 text-sm text-slate-600">
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Create flashcard decks for any topic
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Review sessions that fit your schedule
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Simple progress tracking to stay consistent
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Focused on clarity and ease of use
                </li>
            </ul>
        </div>

        {{-- How it works card --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mb-3">How it works</h2>
            <ol class="space-y-3 text-sm text-slate-600">
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    Create a deck for your subject
                </li>
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    Add cards (questions &amp; answers)
                </li>
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    Review regularly with your plan
                </li>
            </ol>
        </div>

        {{-- For who card --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mb-3">For who</h2>
            <p class="text-sm text-slate-600 leading-relaxed">
                Built for students and parents who want a straightforward way to practice and retain knowledge.
            </p>
        </div>
    </section>

    {{-- FAQ teaser --}}
    <section class="text-center py-10 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900 mb-2">Questions?</h2>
        <p class="text-slate-600 mb-4">See common answers in the FAQ.</p>
        <a href="{{ route('web.help.faq') }}"
           class="inline-block px-6 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800 transition">
            View FAQ
        </a>
    </section>

</x-web-layout>
