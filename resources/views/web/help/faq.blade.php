<x-web-layout title="FAQ" metaDescription="Find answers to your questions about MemoSpark quickly.">

    <div class="max-w-3xl mx-auto">

        {{-- Back link --}}
        <a href="{{ route('web.help') }}" class="text-sm text-slate-500 hover:text-slate-700 mb-4 inline-block">&larr; Back to Help</a>

        <h1 class="text-3xl font-bold text-slate-900 mb-2">Frequently Asked Questions</h1>
        <p class="text-lg text-slate-600 mb-10">Find answers to your questions quickly.</p>

        {{-- FAQ Accordion --}}
        <div class="space-y-4" x-data="{ openCategory: null, openItem: null }">

            @php
            $categories = [
                ['key' => 'general', 'icon' => '&#128241;', 'title' => 'General', 'items' => [
                    ['q' => 'What is MemoSpark?', 'a' => 'MemoSpark is a learning app based on flashcards and spaced repetition. It helps students memorize any type of content effectively, and allows parents to track their children\'s progress.'],
                    ['q' => 'Is MemoSpark free?', 'a' => 'Yes! MemoSpark is free with all basic features: deck creation, review, access to the public library. An optional Premium version offers advanced features.'],
                    ['q' => 'What devices can I use MemoSpark on?', 'a' => 'MemoSpark is available on Android via the Google Play Store. An iOS version is coming soon.'],
                    ['q' => 'Can I use MemoSpark without internet?', 'a' => 'Yes, decks you\'ve already accessed are available offline. Synchronization happens automatically when you reconnect.'],
                ]],
                ['key' => 'account', 'icon' => '&#128100;', 'title' => 'Account & Login', 'items' => [
                    ['q' => 'How do I create an account?', 'a' => 'Download the app, tap "Sign Up", choose your role (Learner or Parent), fill in your information, and confirm your email.'],
                    ['q' => 'Can I sign in with Google?', 'a' => 'Yes! You can create an account or sign in quickly with your Google account.'],
                    ['q' => 'I forgot my password, what should I do?', 'a' => 'On the login screen, tap "Forgot password?". Enter your email and you\'ll receive a link to reset your password.'],
                    ['q' => 'How do I change my personal information?', 'a' => 'Go to Profile > Settings to modify your name, email, profile photo, or password.'],
                    ['q' => 'How do I delete my account?', 'a' => 'Contact us at support@memospark.net with your deletion request. We\'ll process it within 48 hours.'],
                ]],
                ['key' => 'decks', 'icon' => '&#128218;', 'title' => 'Decks & Cards', 'items' => [
                    ['q' => 'How do I create a deck?', 'a' => 'Tap the "+" button in the Decks tab. Give your deck a title, add a description, and start creating your cards.'],
                    ['q' => 'How do I add cards to a deck?', 'a' => 'Open your deck, tap "Add card", then enter the question (front) and answer (back).'],
                    ['q' => 'Can I add images to my cards?', 'a' => 'Yes, you can add images to both the front and back of your cards.'],
                    ['q' => 'How do I share a deck?', 'a' => 'Open your deck, go to settings and enable the "Public" option. Your deck will then be visible in the public library.'],
                    ['q' => 'How do I use AI generation?', 'a' => 'When creating a deck, tap "Generate with AI". Enter a topic and the number of cards you want. The AI will automatically create questions and answers.'],
                    ['q' => 'How many decks can I create?', 'a' => 'There\'s no limit to the number of decks you can create!'],
                ]],
                ['key' => 'learning', 'icon' => '&#129504;', 'title' => 'Review & Learning', 'items' => [
                    ['q' => 'What is spaced repetition?', 'a' => 'It\'s a scientific learning method. Cards you know well appear less often, while those you struggle with come back more frequently. This optimizes your long-term memorization.'],
                    ['q' => 'How does the review system work?', 'a' => 'During review, you see the question, then reveal the answer. You then indicate if you knew it (easy, correct, difficult, review again). The algorithm adjusts when the card appears next.'],
                    ['q' => 'How long should I review each day?', 'a' => 'We recommend 10-15 minutes per day for optimal results. Consistency is more important than duration!'],
                    ['q' => 'Can I see my statistics?', 'a' => 'Yes! In the Statistics tab, you can see your study time, number of cards reviewed, your consecutive day streak, and more.'],
                ]],
                ['key' => 'family', 'icon' => '&#128106;', 'title' => 'Parent / Family Mode', 'items' => [
                    ['q' => 'How do I add my child to my parent account?', 'a' => 'Your child must first create their own "Learner" account. In your Parent space, go to "Children" > "Add a child", search for your child\'s account, send an invitation, and your child accepts from their app.'],
                    ['q' => 'What can I see as a parent?', 'a' => 'You can see your children\'s daily study time, the decks they\'re studying, their scores and progression, and their badges and rewards.'],
                    ['q' => 'Can I create decks for my children?', 'a' => 'Yes! You can create decks and assign them directly to your children.'],
                    ['q' => 'Can my child have multiple linked parents?', 'a' => 'Yes, multiple parent accounts can be linked to the same child (for example, both parents).'],
                    ['q' => 'Can my child see that I\'m their parent?', 'a' => 'Yes, your child can see the list of parents linked to their account in their settings.'],
                ]],
                ['key' => 'premium', 'icon' => '&#11088;', 'title' => 'Premium', 'items' => [
                    ['q' => 'What are the Premium benefits?', 'a' => 'Unlimited AI card generation, no ads, advanced statistics, custom themes, and priority support.'],
                    ['q' => 'How much does Premium cost?', 'a' => 'Monthly: $4.99/month. Annual: $39.99/year (save up to 33%).'],
                    ['q' => 'How do I subscribe to Premium?', 'a' => 'Go to Profile > Premium, choose your plan, and follow the payment instructions via Google Play.'],
                    ['q' => 'How do I cancel my subscription?', 'a' => 'Subscriptions are managed through Google Play. Open Google Play Store > Menu > Subscriptions > Select MemoSpark > Cancel subscription.'],
                    ['q' => 'Can I get a refund?', 'a' => 'Refund requests are handled by Google Play according to their terms. Contact Google Play support for any request.'],
                    ['q' => 'If I cancel, what happens to my data?', 'a' => 'Your data (decks, cards, progression) is kept. You simply lose access to Premium features.'],
                ]],
                ['key' => 'privacy', 'icon' => '&#128274;', 'title' => 'Privacy & Security', 'items' => [
                    ['q' => 'Is my data secure?', 'a' => 'Yes, we take security very seriously. Your data is encrypted and stored on secure servers.'],
                    ['q' => 'Does MemoSpark share my data?', 'a' => 'No, we never sell your personal data. See our Privacy Policy for more details.'],
                    ['q' => 'Is my child\'s data protected?', 'a' => 'Yes, we comply with COPPA and GDPR regulations for protecting minors\' data.'],
                    ['q' => 'How do I delete my data?', 'a' => 'Contact us at support@memospark.net to request complete deletion of your data.'],
                ]],
                ['key' => 'support', 'icon' => '&#127384;', 'title' => 'Problems & Support', 'items' => [
                    ['q' => 'The app isn\'t working, what should I do?', 'a' => 'Try these steps: close and reopen the app, check your internet connection, update the app from the Play Store, restart your device. If the problem persists, contact us.'],
                    ['q' => 'I lost my decks, how do I recover them?', 'a' => 'Your decks are synced with your account. Make sure you\'re logged in with the correct account. If the problem persists, contact support.'],
                    ['q' => 'How do I report a bug?', 'a' => 'Contact us at support@memospark.net describing the problem, your device, and the app version.'],
                    ['q' => 'How do I suggest a feature?', 'a' => 'We love suggestions! Send your ideas to support@memospark.net.'],
                    ['q' => 'How do I contact support?', 'a' => 'Email: support@memospark.net. Contact page: memospark.net/contact. We typically respond within 24-48 hours.'],
                ]],
            ];
            @endphp

            @foreach($categories as $catIndex => $cat)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    {{-- Category header --}}
                    <button @click="openCategory = openCategory === '{{ $cat['key'] }}' ? null : '{{ $cat['key'] }}'; openItem = null"
                            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-slate-50 transition">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">{!! $cat['icon'] !!}</span>
                            <span class="font-semibold text-slate-900">{{ $cat['title'] }}</span>
                            <span class="text-xs text-slate-400">({{ count($cat['items']) }})</span>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transition-transform" :class="openCategory === '{{ $cat['key'] }}' && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Questions --}}
                    <div x-show="openCategory === '{{ $cat['key'] }}'" x-cloak x-collapse>
                        <div class="border-t border-slate-100">
                            @foreach($cat['items'] as $itemIndex => $item)
                                <div class="border-b border-slate-50 last:border-b-0">
                                    <button @click="openItem = openItem === '{{ $cat['key'] }}-{{ $itemIndex }}' ? null : '{{ $cat['key'] }}-{{ $itemIndex }}'"
                                            class="w-full px-6 py-3 flex items-center justify-between text-left text-sm hover:bg-slate-50 transition">
                                        <span class="text-slate-700 font-medium pr-4">{{ $item['q'] }}</span>
                                        <svg class="w-4 h-4 text-slate-300 shrink-0 transition-transform" :class="openItem === '{{ $cat['key'] }}-{{ $itemIndex }}' && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="openItem === '{{ $cat['key'] }}-{{ $itemIndex }}'" x-cloak x-collapse>
                                        <p class="px-6 pb-4 text-sm text-slate-600 leading-relaxed">{{ $item['a'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- Contact CTA --}}
        <div class="mt-10 bg-slate-100 rounded-2xl p-6 text-center">
            <p class="text-slate-900 font-semibold mb-1">Didn't find the answer to your question?</p>
            <p class="text-sm text-slate-600 mb-4">We're here to help!</p>
            <div class="space-y-2 text-sm">
                <p><a href="mailto:support@memospark.net" class="text-emerald-600 hover:underline font-medium">support@memospark.net</a></p>
                <p><a href="{{ route('web.contact') }}" class="text-emerald-600 hover:underline">Contact Form</a></p>
                <p class="text-slate-400">&#128172; We respond within 24-48 hours</p>
            </div>
        </div>

    </div>

</x-web-layout>
