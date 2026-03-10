<x-web-layout title="Contact" metaDescription="Send us a message and we will get back to you.">

    <div class="max-w-xl mx-auto">

        <h1 class="text-3xl font-bold text-slate-900 mb-2">Contact</h1>
        <p class="text-slate-600 mb-8">Send us a message and we will get back to you.</p>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <form method="POST" action="{{ route('web.contact.send') }}" class="space-y-5">
                @csrf

                {{-- Honeypot (hidden) --}}
                <div class="hidden" aria-hidden="true">
                    <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
                </div>

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Subject --}}
                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                           class="w-full border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    @error('subject')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Message --}}
                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700 mb-1">Message</label>
                    <textarea name="message" id="message" rows="5" required
                              class="w-full border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full py-3 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition">
                    Send message
                </button>
            </form>
        </div>

    </div>

</x-web-layout>
