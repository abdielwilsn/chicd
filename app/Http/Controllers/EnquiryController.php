<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    /**
     * Store an enquiry sent from a public website form and notify CHICD.
     */
    public function store(StoreEnquiryRequest $request): RedirectResponse
    {
        $enquiry = Enquiry::create($request->safe()->except('website'));

        Mail::to(config('chicd.enquiries_email'))->queue(new EnquiryReceived($enquiry));

        return back();
    }
}
