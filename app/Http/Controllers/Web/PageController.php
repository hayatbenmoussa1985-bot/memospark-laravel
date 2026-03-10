<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class PageController extends Controller
{
    /**
     * Home page.
     */
    public function home()
    {
        return view('web.home');
    }

    /**
     * Getting started guide.
     */
    public function guide()
    {
        return view('web.guide');
    }

    /**
     * Help hub.
     */
    public function help()
    {
        return view('web.help.index');
    }

    /**
     * Get started page.
     */
    public function getStarted()
    {
        return view('web.help.get-started');
    }

    /**
     * FAQ page.
     */
    public function faq()
    {
        return view('web.help.faq');
    }

    /**
     * Video tutorials (coming soon).
     */
    public function videoTutorials()
    {
        return view('web.help.video-tutorials');
    }

    /**
     * Privacy Policy.
     */
    public function privacy()
    {
        return view('web.privacy-policy');
    }

    /**
     * Terms of Service.
     */
    public function terms()
    {
        return view('web.terms-of-service');
    }

    /**
     * Blog listing.
     */
    public function blogIndex()
    {
        $posts = BlogPost::where('status', 'published')
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->paginate(12);

        return view('web.blog.index', compact('posts'));
    }

    /**
     * Blog post detail.
     */
    public function blogShow(string $slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('web.blog.show', compact('post'));
    }
}
