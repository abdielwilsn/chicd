<?php

namespace App\Models;

use App\Enums\EnquiryType;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * An enquiry submitted through one of the public website forms.
 *
 * Personal details and the message are encrypted at rest because some
 * enquiries (such as Safe Housing requests) describe sensitive circumstances.
 *
 * @property int $id
 * @property EnquiryType $type
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $organisation
 * @property string|null $subject
 * @property string $message
 * @property Carbon|null $handled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['type', 'name', 'email', 'phone', 'organisation', 'subject', 'message'])]
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EnquiryType::class,
            'name' => 'encrypted',
            'email' => 'encrypted',
            'phone' => 'encrypted',
            'message' => 'encrypted',
            'handled_at' => 'datetime',
        ];
    }
}
