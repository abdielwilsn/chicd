<?php

namespace App\Models;

use App\Enums\DonationFrequency;
use App\Enums\DonationStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A gift made through the website and paid with Paystack.
 *
 * Amounts are in the currency's smallest unit (kobo for naira), as Paystack
 * reports them. The fund label is kept as it was when the donor gave, so the
 * record stays right if a programme is later renamed or removed.
 *
 * Each later payment of a recurring gift is its own donation, linked to the
 * first payment through parent_id.
 *
 * @property int $id
 * @property string $reference
 * @property int|null $parent_id
 * @property string $fund
 * @property string $fund_label
 * @property DonationFrequency $frequency
 * @property int $amount
 * @property string $currency
 * @property string $donor_name
 * @property string $donor_email
 * @property string|null $donor_phone
 * @property DonationStatus $status
 * @property int|null $paystack_transaction_id
 * @property string|null $paystack_customer_code
 * @property string|null $paystack_plan_code
 * @property string|null $paystack_subscription_code
 * @property string|null $channel
 * @property int|null $fees
 * @property CarbonImmutable|null $paid_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable([
    'reference', 'parent_id', 'fund', 'fund_label', 'frequency', 'amount', 'currency',
    'donor_name', 'donor_email', 'donor_phone', 'paystack_customer_code', 'paystack_plan_code',
])]
class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    /**
     * Whether Paystack has confirmed the payment.
     */
    public function isSuccessful(): bool
    {
        return $this->status === DonationStatus::Successful;
    }

    /**
     * Whether this is a later payment of a recurring gift.
     */
    public function isRenewal(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Mark the donation as paid with the details Paystack confirmed. Only the
     * first confirmation counts: Paystack may confirm a payment more than
     * once (on the donor's return and by webhook), and the caller should only
     * send a receipt when this returns true.
     *
     * @param  array{paystack_transaction_id: int|null, paystack_customer_code: string|null, channel: string|null, fees: int|null, paid_at: CarbonInterface}  $details
     */
    public function markSuccessful(array $details): bool
    {
        $updated = static::query()
            ->whereKey($this->id)
            ->where('status', '!=', DonationStatus::Successful)
            ->update([...$details, 'status' => DonationStatus::Successful->value]);

        $this->refresh();

        return $updated === 1;
    }

    /**
     * The amount in the main currency unit (naira), formatted for display,
     * such as "₦50,000.00".
     */
    public function formattedAmount(): string
    {
        return static::formatMoney($this->amount, $this->currency);
    }

    /**
     * Format an amount in the currency's smallest unit for display.
     */
    public static function formatMoney(int $amount, string $currency): string
    {
        $prefix = $currency === 'NGN' ? '₦' : $currency.' ';

        return $prefix.number_format($amount / 100, 2);
    }

    /**
     * Donations Paystack has confirmed.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function successful(Builder $query): void
    {
        $query->where('status', DonationStatus::Successful);
    }

    /**
     * Donations paid in the month starting at the given moment (organisation
     * time).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function paidInMonth(Builder $query, CarbonInterface $month): void
    {
        $query->whereBetween('paid_at', [$month->utc(), $month->endOfMonth()->utc()]);
    }

    /**
     * The first payment of the recurring gift this payment belongs to.
     *
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Later payments of this recurring gift.
     *
     * @return HasMany<self, $this>
     */
    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'frequency' => DonationFrequency::class,
            'status' => DonationStatus::class,
            'amount' => 'integer',
            'fees' => 'integer',
            'paystack_transaction_id' => 'integer',
            'paid_at' => 'datetime',
        ];
    }
}
