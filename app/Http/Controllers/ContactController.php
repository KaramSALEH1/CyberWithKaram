<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function show()
    {
        return view('contact');
    }

    /**
     * Store a contact enquiry.
     *
     * The message is validated then forwarded to the CyberLogia Telegram
     * operations channel. Notification delivery is best-effort, so a Telegram
     * outage never loses the enquiry or errors the user.
     */
    public function store(Request $request, TelegramService $telegram): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = implode("\n", [
            '<b>New CyberLogia Enquiry</b>',
            'Name    : '.e($data['name']),
            'Email   : '.e($data['email']),
            'Subject : '.e($data['subject']),
            '',
            e($data['message']),
        ]);

        $telegram->sendMessage($message);

        return redirect()
            ->route('contact')
            ->with('status', 'Thanks! Your enquiry has been received — we will reply within one business day.');
    }
}