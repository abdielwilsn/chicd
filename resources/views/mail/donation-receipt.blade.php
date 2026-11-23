<x-mail::message>
# Thank you, {{ strtok($donation->donor_name, ' ') ?: $donation->donor_name }}

We have received your gift to {{ config('chicd.name') }}. Your support helps us address barriers to healthcare, education, safety and community well-being.

<x-mail::table>
| Receipt | |
|:--|:--|
| **Receipt number** | {{ $donation->reference }} |
| **Date** | {{ $paidOn }} |
| **Amount** | {{ $donation->formattedAmount() }} |
| **Supporting** | {{ $donation->fund_label }} |
| **Frequency** | {{ $donation->frequency->label() }}{{ $donation->isRenewal() ? ' (repeat payment)' : '' }} |
| **Donor** | {{ $donation->donor_name }} |
@if ($donation->channel)
| **Paid by** | {{ ucfirst(str_replace('_', ' ', $donation->channel)) }} |
@endif
</x-mail::table>

@if ($donation->frequency->isRecurring() && ! $donation->isRenewal())
This is a {{ strtolower($donation->frequency->label()) }} gift. Paystack will charge {{ $donation->formattedAmount() }} on the same schedule until you cancel, and you will receive a receipt from us each time. To change or cancel your gift at any time, reply to this email.
@endif

Please keep this email as your receipt. If anything here is not right, reply to this email and quote the receipt number.

With gratitude,<br>
The {{ config('chicd.short_name') }} team
</x-mail::message>
