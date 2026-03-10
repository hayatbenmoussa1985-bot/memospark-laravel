<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * List blog posts.
     */
    public function index(Request $request)
    {
        $query = BlogPost::with('author');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $posts = $query->latest()->paginate(20)->withQueryString();

        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Create post form.
     */
    public function create()
    {
        return view('admin.blog.create');
    }

    /**
     * Store new blog post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();

        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (BlogPost::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter++;
        }

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')
                ->store('blog/covers', 'public');
        }
        unset($validated['cover_image']);

        // Set published_at
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        AuditLog::record(
            action: 'blog_post_created',
            targetType: 'blog_post',
            targetId: $post->id,
            newValues: ['title' => $post->title, 'status' => $post->status],
        );

        return redirect()
            ->route('admin.blog.index')
            ->with('success', "Post \"{$post->title}\" created.");
    }

    /**
     * Edit post form.
     */
    public function edit(BlogPost $post)
    {
        return view('admin.blog.edit', compact('post'));
    }

    /**
     * Update blog post.
     */
    public function update(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:blog_posts,slug,' . $post->id],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $oldValues = $post->only(['title', 'status']);

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')
                ->store('blog/covers', 'public');
        }
        unset($validated['cover_image']);

        // Set published_at if publishing for first time
        if ($validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        AuditLog::record(
            action: 'blog_post_updated',
            targetType: 'blog_post',
            targetId: $post->id,
            oldValues: $oldValues,
            newValues: ['title' => $post->title, 'status' => $post->status],
        );

        return redirect()
            ->route('admin.blog.edit', $post)
            ->with('success', 'Post updated.');
    }

    /**
     * Delete blog post.
     */
    public function destroy(BlogPost $post)
    {
        AuditLog::record(
            action: 'blog_post_deleted',
            targetType: 'blog_post',
            targetId: $post->id,
            oldValues: ['title' => $post->title],
        );

        $post->delete();

        return redirect()
            ->route('admin.blog.index')
            ->with('success', 'Post deleted.');
    }
}
