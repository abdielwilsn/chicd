<x-mail::message>
# {{ $enquiry->type->label() }}

A new message was sent through the CHICD website on {{ $enquiry->created_at?->format('j F Y \a\t H:i') }}.

@if ($enquiry->type->isConfidential())
<x-mail::panel>
This message may describe sensitive personal circumstances. Please handle it in line with CHICD's confidentiality and safeguarding procedures and do not forward it outside the responsible team.
</x-mail::panel>
@endif

<x-mail::table>
| | |
|:--|:--|
| **Name** | {{ $enquiry->name }} |
@if ($enquiry->email)
| **Email** | {{ $enquiry->email }} |
@endif
@if ($enquiry->phone)
| **Phone** | {{ $enquiry->phone }} |
@endif
@if ($enquiry->organisation)
| **Organisation** | {{ $enquiry->organisation }} |
@endif
@if ($enquiry->subject)
| **Subject** | {{ $enquiry->subject }} |
@endif
</x-mail::table>

**Message**

{{ $enquiry->message }}

Reference: #{{ $enquiry->id }}

{{ config('chicd.short_name') }} website
</x-mail::message>
