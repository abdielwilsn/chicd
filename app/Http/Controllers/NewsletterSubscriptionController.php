<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsletterSubscriptionRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriptionController extends Controller
{
    /**
     * Subscribe an email address to CHICD updates.
     *
     * Existing subscribers receive the same response so the form does not
     * reveal which addresses are already on the list.
     */
    public function store(StoreNewsletterSubscriptionRequest $request): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => strtolower($request->validated('email'))],
            ['name' => $request->validated('name')],
        );

        if ($subscriber->unsubscribed_at !== null) {
            $subscriber->forceFill(['unsubscribed_at' => null])->save();
        }

        return back();
    }
}
