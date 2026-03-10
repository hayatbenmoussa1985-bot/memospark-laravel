<x-web-layout title="Get Started" metaDescription="Learn how to use MemoSpark in just a few minutes.">

    <div class="max-w-3xl mx-auto">

        {{-- Back link --}}
        <a href="{{ route('web.help') }}" class="text-sm text-slate-500 hover:text-slate-700 mb-4 inline-block">&larr; Back to Help</a>

        <h1 class="text-3xl font-bold text-slate-900 mb-2">Get Started with MemoSpark</h1>
        <p class="text-lg text-slate-600 mb-10">Learn how to use MemoSpark in just a few minutes.</p>

        {{-- Step 1: Download --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">&#128241; Step 1: Download the App</h2>
            <ul class="space-y-2 text-slate-600 mb-4">
                <li>The app is free and available on Android.</li>
                <li>iOS version coming soon.</li>
            </ul>
            <div class="flex flex-wrap gap-3">
                <a href="#" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">
                    Get it on Google Play
                </a>
                <span class="px-5 py-2.5 bg-slate-100 text-slate-400 text-sm font-medium rounded-lg cursor-not-allowed">
                    App Store (Coming Soon)
                </span>
            </div>
        </div>

        {{-- Step 2: Account --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">&#128100; Step 2: Create Your Account</h2>
            <ol class="space-y-2 text-slate-600 list-decimal list-inside">
                <li>Open the MemoSpark app</li>
                <li>Tap &quot;Sign Up&quot;</li>
                <li>Choose your role: &#127891; Learner (to study and review) or &#128106; Parent (to track your children's progress)</li>
                <li>Fill in your information</li>
                <li>Confirm your email</li>
            </ol>
            <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
                <p class="text-sm text-amber-800"><strong>Tip:</strong> You can also sign in with Google for quick access!</p>
            </div>
        </div>

        {{-- For Learners --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">&#127919; For Learners: Start Learning</h2>
            <div class="space-y-4 text-slate-600">
                <div class="flex items-start gap-3">
                    <span class="text-lg shrink-0">&#128218;</span>
                    <p><strong>Explore the Library</strong>: Browse public decks created by the community and find topics that interest you.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-lg shrink-0">&#9999;&#65039;</span>
                    <p><strong>Create your first deck</strong>: Tap &quot;+&quot; to create a new deck, add a title and description, then create your cards (question/answer).</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-lg shrink-0">&#129504;</span>
                    <p><strong>Review smartly</strong>: The spaced repetition algorithm optimizes your memorization. Review a few minutes each day for better results.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-lg shrink-0">&#128200;</span>
                    <p><strong>Track your progress</strong>: Check your statistics and earn badges by reaching your goals.</p>
                </div>
            </div>
        </div>

        {{-- For Parents --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">&#128106; For Parents: Supervise Your Children's Learning</h2>
            <div class="space-y-4 text-slate-600">
                <div class="flex items-start gap-3">
                    <span class="text-lg shrink-0">&#128118;</span>
                    <p><strong>Add your children</strong>: Go to the &quot;Children&quot; tab, search for your child's account or create one, then send an invitation.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-lg shrink-0">&#128200;</span>
                    <p><strong>Track their progress</strong>: View daily study time, check studied decks, and see scores and progression.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-lg shrink-0">&#128221;</span>
                    <p><strong>Assign decks</strong>: Create custom decks for your children or assign decks from the library.</p>
                </div>
            </div>
        </div>

        {{-- Feature highlights --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">&#10024; What You Can Do with MemoSpark</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach([
                    ['icon' => '&#128218;', 'title' => 'Public Library', 'desc' => 'Access thousands of decks created by the community'],
                    ['icon' => '&#129302;', 'title' => 'AI Generation', 'desc' => 'Automatically create cards from a topic'],
                    ['icon' => '&#128260;', 'title' => 'Spaced Repetition', 'desc' => 'Scientific algorithm for optimal memorization'],
                    ['icon' => '&#128106;', 'title' => 'Family Mode', 'desc' => 'Parents can track their children\'s progress'],
                    ['icon' => '&#127942;', 'title' => 'Badges & Rewards', 'desc' => 'Stay motivated with a gamification system'],
                    ['icon' => '&#128200;', 'title' => 'Detailed Statistics', 'desc' => 'Track your progress over time'],
                ] as $feature)
                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                        <span class="text-xl shrink-0">{!! $feature['icon'] !!}</span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $feature['title'] }}</p>
                            <p class="text-xs text-slate-500">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick FAQ --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">&#10067; Quick FAQ</h2>
            <div class="space-y-4">
                @foreach([
                    ['q' => 'Is MemoSpark free?', 'a' => 'Yes! The app is free with basic features. A Premium version is available for advanced features.'],
                    ['q' => 'Can I use MemoSpark offline?', 'a' => 'Downloaded decks are accessible offline.'],
                    ['q' => 'How can my child join my parent account?', 'a' => 'Create a child account, then search for it from your parent space to send an invitation.'],
                    ['q' => 'Is my data secure?', 'a' => 'Yes, we take privacy very seriously. Check our Privacy Policy for more details.'],
                ] as $faq)
                    <div class="border-b border-slate-100 pb-3">
                        <p class="text-sm font-semibold text-slate-900 mb-1">{{ $faq['q'] }}</p>
                        <p class="text-sm text-slate-600">{{ $faq['a'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Need help --}}
        <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-6 text-center">
            <h2 class="text-lg font-bold text-slate-900 mb-2">&#127384; Need Help?</h2>
            <div class="space-y-1 text-sm text-slate-600 mb-4">
                <p>Email: <a href="mailto:support@memospark.net" class="text-emerald-600 hover:underline">support@memospark.net</a></p>
                <p><a href="{{ route('web.help') }}" class="text-emerald-600 hover:underline">Help Center</a> &middot; <a href="{{ route('web.privacy') }}" class="text-emerald-600 hover:underline">Privacy Policy</a></p>
            </div>
        </div>

    </div>

</x-web-layout>
