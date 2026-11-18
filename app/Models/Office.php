<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\OfficeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A CHICD office with a circular geofence. Office clock-ins and clock-outs
 * are checked against the nearest office's geofence.
 *
 * @property int $id
 * @property string $name
 * @property float $latitude
 * @property float $longitude
 * @property int $geofence_radius_metres
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'latitude', 'longitude', 'geofence_radius_metres'])]
class Office extends Model
{
    /** @use HasFactory<OfficeFactory> */
    use HasFactory;

    /**
     * Mean radius of the Earth, used by the haversine formula.
     */
    private const EARTH_RADIUS_METRES = 6_371_000;

    /**
     * Find the office closest to a point, or null when no office exists.
     */
    public static function nearestTo(float $latitude, float $longitude): ?self
    {
        return static::query()
            ->get()
            ->sortBy(fn (self $office): float => $office->distanceInMetresTo($latitude, $longitude))
            ->first();
    }

    /**
     * Check a point against an office geofence: the given office, or else the
     * nearest one. A point with no office to check against is outside.
     *
     * @return array{office_id: int|null, distance_metres: int|null, within_geofence: bool}
     */
    public static function checkGeofence(float $latitude, float $longitude, ?self $office = null): array
    {
        $office ??= static::nearestTo($latitude, $longitude);

        if ($office === null) {
            return ['office_id' => null, 'distance_metres' => null, 'within_geofence' => false];
        }

        return [
            'office_id' => $office->id,
            'distance_metres' => (int) round($office->distanceInMetresTo($latitude, $longitude)),
            'within_geofence' => $office->geofenceContains($latitude, $longitude),
        ];
    }

    /**
     * Great-circle distance from the office to a point, in metres.
     */
    public function distanceInMetresTo(float $latitude, float $longitude): float
    {
        $latitudeDelta = deg2rad($latitude - $this->latitude);
        $longitudeDelta = deg2rad($longitude - $this->longitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) * sin($longitudeDelta / 2) ** 2;

        return self::EARTH_RADIUS_METRES * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Whether a point falls inside the office geofence.
     */
    public function geofenceContains(float $latitude, float $longitude): bool
    {
        return $this->distanceInMetresTo($latitude, $longitude) <= $this->geofence_radius_metres;
    }

    /**
     * Office attendance records verified against this office.
     *
     * @return HasMany<OfficeAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(OfficeAttendance::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'geofence_radius_metres' => 'integer',
        ];
    }
}
