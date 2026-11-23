<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

/**
 * Pay amounts (whole kobo) stored encrypted, for models holding salaries.
 * Laravel's encrypted casts cover strings and structures, not integers.
 */
trait EncryptsAmounts
{
    /**
     * An attribute holding an encrypted whole number of kobo, or null.
     *
     * @return Attribute<int|null, int|null>
     */
    protected static function encryptedAmount(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?int => $value === null ? null : (int) Crypt::decryptString($value),
            set: fn (?int $value): ?string => $value === null ? null : Crypt::encryptString((string) $value),
        );
    }
}
