<?php

namespace App\Concerns;

use App\Models\CorrectionRequest;
use App\Models\RecordChange;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Gives a model an audit trail: every admin change is kept as a RecordChange
 * with who made it, why, and the old and new values.
 */
trait HasRecordChanges
{
    /**
     * The audit trail for this record, newest first.
     *
     * @return MorphMany<RecordChange, $this>
     */
    public function recordChanges(): MorphMany
    {
        return $this->morphMany(RecordChange::class, 'auditable')->latest('created_at')->latest('id');
    }

    /**
     * Record a change to this record in its audit trail.
     *
     * @param  array<string, array{from: mixed, to: mixed}>  $changes
     */
    public function logChange(User $changedBy, string $action, string $reason, array $changes = [], ?CorrectionRequest $correctionRequest = null): RecordChange
    {
        return $this->recordChanges()->create([
            'changed_by' => $changedBy->id,
            'correction_request_id' => $correctionRequest?->id,
            'action' => $action,
            'changes' => $changes === [] ? null : $changes,
            'reason' => $reason,
        ]);
    }
}
