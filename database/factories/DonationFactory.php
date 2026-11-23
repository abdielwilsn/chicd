<?php

namespace Database\Factories;

use App\Enums\DonationFrequency;
use App\Enums\DonationStatus;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => 'CHICD-'.Str::ulid(),
            'fund' => 'general',
            'fund_label' => 'Donate to CHICD (where it is needed most)',
            'frequency' => DonationFrequency::OneTime,
            'amount' => fake()->randomElement([500000, 1000000, 2500000, 5000000]),
            'currency' => 'NGN',
            'donor_name' => fake()->name(),
            'donor_email' => fake()->unique()->safeEmail(),
            'donor_phone' => null,
            'status' => DonationStatus::Pending,
        ];
    }

    /**
     * A donation Paystack has confirmed.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DonationStatus::Successful,
            'paystack_transaction_id' => fake()->unique()->numberBetween(1000000, 9999999),
            'paystack_customer_code' => 'CUS_'.Str::lower(Str::random(15)),
            'channel' => 'card',
            'fees' => 15000,
            'paid_at' => now(),
        ]);
    }

    /**
     * The first payment of a monthly gift.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => DonationFrequency::Monthly,
            'paystack_plan_code' => 'PLN_'.Str::lower(Str::random(15)),
        ]);
    }

    /**
     * A gift to the Show Up Ready to Learn programme.
     */
    public function forSurl(): static
    {
        return $this->state(fn (array $attributes) => [
            'fund' => 'surl',
            'fund_label' => 'Show Up Ready to Learn (SURL)',
        ]);
    }
}
