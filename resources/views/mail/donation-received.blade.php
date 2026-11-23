<x-mail::message>
# New donation received

Paystack has confirmed a donation made through the CHICD website on {{ $paidOn }}.

<x-mail::table>
| | |
|:--|:--|
| **Amount** | {{ $donation->formattedAmount() }} |
| **Supporting** | {{ $donation->fund_label }} |
| **Frequency** | {{ $donation->frequency->label() }}{{ $donation->isRenewal() ? ' (repeat payment)' : '' }} |
| **Donor** | {{ $donation->donor_name }} |
| **Email** | {{ $donation->donor_email }} |
@if ($donation->donor_phone)
| **Phone** | {{ $donation->donor_phone }} |
@endif
@if ($donation->channel)
| **Paid by** | {{ ucfirst(str_replace('_', ' ', $donation->channel)) }} |
@endif
| **Reference** | {{ $donation->reference }} |
</x-mail::table>

The donor has been sent a receipt. Every donation is listed under Donations in the staff portal, where it can also be exported for reconciliation.

<x-mail::button :url="route('admin.donations.index')">
View donations
</x-mail::button>

{{ config('chicd.short_name') }} website
</x-mail::message>
