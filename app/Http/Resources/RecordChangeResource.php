<?php

namespace App\Http\Resources;

use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\RecordChange;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * An audit trail entry. Load auditable and changedBy first.
 *
 * @mixin RecordChange
 */
class RecordChangeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, action: string, record: string, reason: string, changed_by: string, changed_at: string, changes: list<array{field: string, from: string, to: string}>, correction_request_id: int|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => Str::ucfirst($this->action),
            'record' => match (true) {
                $this->auditable instanceof OfficeAttendance => 'Office session',
                $this->auditable instanceof FieldActivity => 'Field activity: '.$this->auditable->activity,
                $this->auditable instanceof Timesheet => 'Timesheet',
                default => 'Deleted record',
            },
            'reason' => $this->reason,
            'changed_by' => $this->changedBy->name ?? 'A removed account',
            'changed_at' => $this->created_at->setTimezone(config('chicd.timezone'))->format('j M Y, H:i'),
            'changes' => array_map(
                fn (array $change, string $field): array => [
                    'field' => Str::of($field)->replace('_', ' ')->ucfirst()->toString(),
                    'from' => $this->display($change['from']),
                    'to' => $this->display($change['to']),
                ],
                $this->changes ?? [],
                array_keys($this->changes ?? []),
            ),
            'correction_request_id' => $this->correction_request_id,
        ];
    }

    /**
     * A stored value as text, with an empty value shown as a dash.
     */
    private function display(mixed $value): string
    {
        return $value === null || $value === '' ? '—' : (string) $value;
    }
}
