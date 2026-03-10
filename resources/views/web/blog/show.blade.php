<x-web-layout :title="$post->title" :metaDescription="$post->excerpt ?? Str::limit(strip_tags($post->content), 160)">

    <div class="max-w-3xl mx-auto">

        {{-- Back link --}}
        <a href="{{ route('web.blog.index') }}" class="text-sm text-slate-500 hover:text-slate-700 mb-6 inline-block">&larr; Back to Blog</a>

        {{-- Cover image --}}
        @if($post->cover_image_path)
            <div class="rounded-2xl overflow-hidden mb-8">
                <img src="{{ Storage::url($post->cover_image_path) }}" alt="{{ $post->title }}"
                     class="w-full h-64 sm:h-80 object-cover">
            </div>
        @endif

        {{-- Post header --}}
        <header class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 mb-4 leading-tight">{{ $post->title }}</h1>
            <div class="flex items-center gap-3 text-sm text-slate-500">
                @if($post->author)
                    <span>By <strong class="text-slate-700">{{ $post->author->name }}</strong></span>
                    <span>&middot;</span>
                @endif
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F d, Y') }}</time>
            </div>
        </header>

        {{-- Post content --}}
        <article class="prose prose-slate prose-lg max-w-none">
            {!! $post->content !!}
        </article>

        {{-- Bottom navigation --}}
        <div class="mt-12 pt-8 border-t border-slate-200 text-center">
            <a href="{{ route('web.blog.index') }}" class="text-sm text-emerald-600 hover:underline font-medium">
                &larr; Back to all posts
            </a>
        </div>

    </div>

</x-web-layout>
