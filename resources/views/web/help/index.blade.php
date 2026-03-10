<x-web-layout title="Help & Support" metaDescription="Find quick answers and support resources for MemoSpark.">

    <div class="max-w-3xl mx-auto">

        <h1 class="text-3xl font-bold text-slate-900 mb-2">Help &amp; Support</h1>
        <p class="text-slate-600 mb-8">Find quick answers and support resources.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Get Started --}}
            <a href="{{ route('web.help.get-started') }}"
               class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:border-emerald-300 hover:shadow-md transition group">
                <div class="text-3xl mb-3">📱</div>
                <h2 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition mb-1">Get Started</h2>
                <p class="text-sm text-slate-500">Setup basics and first steps.</p>
            </a>

            {{-- FAQ --}}
            <a href="{{ route('web.help.faq') }}"
               class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:border-emerald-300 hover:shadow-md transition group">
                <div class="text-3xl mb-3">&#10067;</div>
                <h2 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition mb-1">FAQ</h2>
                <p class="text-sm text-slate-500">Common questions and answers.</p>
            </a>

            {{-- Video Tutorials --}}
            <a href="{{ route('web.help.video-tutorials') }}"
               class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:border-emerald-300 hover:shadow-md transition group">
                <div class="text-3xl mb-3">&#127909;</div>
                <h2 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition mb-1">Video Tutorials</h2>
                <p class="text-sm text-slate-500">Short walkthroughs (coming soon).</p>
            </a>

            {{-- Contact --}}
            <a href="{{ route('web.contact') }}"
               class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:border-emerald-300 hover:shadow-md transition group">
                <div class="text-3xl mb-3">&#9993;</div>
                <h2 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition mb-1">Contact</h2>
                <p class="text-sm text-slate-500">How to reach support.</p>
            </a>

        </div>

    </div>

</x-web-layout>
