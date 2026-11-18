/**
 * An office session as sent by OfficeAttendanceResource. Times are already
 * formatted in the organisation timezone.
 */
export type OfficeAttendance = {
    id: number;
    date: string;
    office: string | null;
    clocked_in_time: string;
    clocked_in_within_geofence: boolean;
    clocked_out_time: string | null;
    clocked_out_within_geofence: boolean | null;
    minutes: number | null;
};

/**
 * A field activity as sent by FieldActivityResource. Times are already
 * formatted in the organisation timezone.
 */
export type FieldActivity = {
    id: number;
    date: string;
    programme: string;
    activity: string;
    description: string | null;
    location: string;
    started_time: string;
    ended_time: string | null;
    minutes: number | null;
};

export type WorkMode = 'office' | 'field';
