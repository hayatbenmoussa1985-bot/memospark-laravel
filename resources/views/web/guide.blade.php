<x-web-layout title="Getting Started Guide" metaDescription="Learn how to create decks, add cards, and build a review routine with MemoSpark.">

    <div class="max-w-3xl mx-auto">

        <h1 class="text-3xl font-bold text-slate-900 mb-3">Getting Started with MemoSpark</h1>
        <p class="text-lg text-slate-600 mb-10">
            This guide covers the basics: creating decks, adding cards, and building a simple review routine.
        </p>

        {{-- Section 1 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">1) Create a deck</h2>
            <ul class="space-y-3 text-slate-600">
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">1</span>
                    Choose a topic (e.g., Biology, Vocabulary, History dates).
                </li>
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">2</span>
                    Give your deck a clear name you will recognize later.
                </li>
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">3</span>
                    Keep decks focused (smaller is easier to review consistently).
                </li>
            </ul>
        </div>

        {{-- Section 2 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">2) Add flashcards</h2>
            <ul class="space-y-3 text-slate-600">
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">1</span>
                    Write a short prompt/question on the front.
                </li>
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">2</span>
                    Write a concise answer on the back.
                </li>
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">3</span>
                    Prefer one idea per card to make recall easier.
                </li>
            </ul>
        </div>

        {{-- Section 3 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">3) Review with a plan</h2>
            <p class="text-slate-600 mb-4">
                Consistent short sessions usually work better than rare long sessions. Start simple:
            </p>
            <ul class="space-y-3 text-slate-600">
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">1</span>
                    Review new cards on the day you add them.
                </li>
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">2</span>
                    Do a quick review the next day.
                </li>
                <li class="flex items-start gap-3">
                    <span class="shrink-0 w-6 h-6 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-xs font-bold mt-0.5">3</span>
                    Continue with regular sessions as your schedule allows.
                </li>
            </ul>
        </div>

        {{-- Need help --}}
        <div class="bg-slate-100 rounded-2xl p-6 text-center">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Need help?</h2>
            <p class="text-slate-600 mb-4">Visit the Help section for FAQs and support.</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('web.help') }}" class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">
                    Help &amp; Support
                </a>
                <a href="{{ route('web.contact') }}" class="px-6 py-2.5 text-sm text-slate-700 border border-slate-300 rounded-xl hover:bg-white">
                    Contact
                </a>
            </div>
        </div>

    </div>

</x-web-layout>
