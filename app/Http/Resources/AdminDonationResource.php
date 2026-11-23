<?php

namespace App\Http\Resources;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A donation as listed for admins.
 *
 * @mixin Donation
 */
class AdminDonationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $timezone = config('chicd.timezone');

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'donor_name' => $this->donor_name,
            'donor_email' => $this->donor_email,
            'donor_phone' => $this->donor_phone,
            'fund' => $this->fund_label,
            'frequency' => $this->frequency->label(),
            'is_recurring' => $this->frequency->isRecurring(),
            'is_renewal' => $this->isRenewal(),
            'amount' => $this->formattedAmount(),
            'fees' => $this->fees === null ? null : Donation::formatMoney($this->fees, $this->currency),
            'channel' => $this->channel === null ? null : ucfirst(str_replace('_', ' ', $this->channel)),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'paid_on' => $this->paid_at?->setTimezone($timezone)->format('j M Y, H:i'),
            'started_on' => $this->created_at?->setTimezone($timezone)->format('j M Y, H:i'),
        ];
    }
}
