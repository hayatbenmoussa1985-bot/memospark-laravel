<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Show contact form.
     */
    public function show()
    {
        return view('web.contact');
    }

    /**
     * Handle contact form submission.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'honeypot' => ['nullable', 'max:0'], // Spam protection
        ]);

        // Check honeypot (spam bot protection)
        if (!empty($validated['honeypot'])) {
            return back()->with('success', 'Thanks — your message has been sent.');
        }

        // Send email to support
        Mail::raw(
            "Name: {$validated['name']}\nEmail: {$validated['email']}\n\n{$validated['message']}",
            function ($mail) use ($validated) {
                $mail->to('support@memospark.net')
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject("[MemoSpark Contact] {$validated['subject']}");
            }
        );

        return back()->with('success', 'Thanks — your message has been sent.');
    }
}
