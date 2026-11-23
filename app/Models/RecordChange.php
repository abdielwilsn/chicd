<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One entry in the audit trail of an attendance record or timesheet. Entries
 * are only ever added, never edited or removed.
 *
 * @property int $id
 * @property string $auditable_type
 * @property int $auditable_id
 * @property int|null $changed_by
 * @property int|null $correction_request_id
 * @property string $action
 * @property array<string, array{from: mixed, to: mixed}>|null $changes
 * @property string $reason
 * @property CarbonImmutable $created_at
 */
#[Fillable(['changed_by', 'correction_request_id', 'action', 'changes', 'reason'])]
class RecordChange extends Model
{
    public const UPDATED_AT = null;

    /**
     * The record that was changed.
     *
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The admin who made the change.
     *
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * The staff request the change answered, if any.
     *
     * @return BelongsTo<CorrectionRequest, $this>
     */
    public function correctionRequest(): BelongsTo
    {
        return $this->belongsTo(CorrectionRequest::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
