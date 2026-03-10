<x-web-layout title="Blog" metaDescription="Latest articles and updates from MemoSpark.">

    <div class="max-w-3xl mx-auto">

        <h1 class="text-3xl font-bold text-slate-900 mb-2">Blog</h1>
        <p class="text-slate-600 mb-8">Latest articles and updates from MemoSpark.</p>

        @if($posts->isNotEmpty())
            <div class="space-y-6">
                @foreach($posts as $post)
                    <a href="{{ route('web.blog.show', $post->slug) }}"
                       class="block bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-emerald-300 hover:shadow-md transition group">
                        @if($post->cover_image_path)
                            <div class="h-48 bg-slate-100 overflow-hidden">
                                <img src="{{ Storage::url($post->cover_image_path) }}" alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @endif
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M d, Y') }}</time>
                                @if($post->author)
                                    <span>&middot;</span>
                                    <span>{{ $post->author->name }}</span>
                                @endif
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 group-hover:text-emerald-700 transition mb-2">{{ $post->title }}</h2>
                            @if($post->excerpt)
                                <p class="text-sm text-slate-600 line-clamp-2">{{ $post->excerpt }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <h2 class="text-lg font-semibold text-slate-900 mb-1">No blog posts yet</h2>
                <p class="text-slate-500">Check back soon for articles and updates!</p>
            </div>
        @endif

    </div>

</x-web-layout>
