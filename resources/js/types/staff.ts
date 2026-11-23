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

export type TimesheetTotals = {
    officeMinutes: number;
    fieldMinutes: number;
    officeDays: number;
    fieldDays: number;
    daysWorked: number;
    flaggedClockings: number;
    openRecords: number;
};

export type TimesheetStatusValue = 'pending' | 'approved';

export type CorrectionStatusValue = 'pending' | 'resolved' | 'declined';

export type CorrectableRecordType = 'office_attendance' | 'field_activity';

/**
 * A request to correct attendance, as sent by CorrectionRequestResource.
 */
export type CorrectionRequest = {
    id: number;
    work_date: string;
    work_date_label: string;
    record: string;
    record_type: CorrectableRecordType | null;
    record_id: number | null;
    details: string;
    status: CorrectionStatusValue;
    status_label: string;
    resolution_note: string | null;
    resolved_by: string | null;
    requested_at: string;
    staff_name: string | null;
    staff_id: number;
};

/**
 * The record a correction request is about, chosen in the staff portal.
 */
export type CorrectableRecord = {
    type: CorrectableRecordType;
    id: number;
    summary: string;
};

export type CapturedLocation = {
    latitude: number;
    longitude: number;
    accuracy_metres: number | null;
    distance_metres: number | null;
};

export type AdminOfficeAttendance = OfficeAttendance & {
    flagged: boolean;
    clock_in_location: CapturedLocation;
    clock_out_location: CapturedLocation | null;
    edit: { clocked_in_at: string; clocked_out_at: string | null };
};

export type AdminFieldActivity = FieldActivity & {
    start_location: CapturedLocation;
    end_location: CapturedLocation | null;
    edit: {
        started_at: string;
        ended_at: string | null;
        programme: string;
        activity: string;
        location: string;
        description: string | null;
    };
};

/**
 * An audit trail entry, as sent by RecordChangeResource.
 */
export type RecordChange = {
    id: number;
    action: string;
    record: string;
    reason: string;
    changed_by: string;
    changed_at: string;
    changes: { field: string; from: string; to: string }[];
    correction_request_id: number | null;
};

/** A staff portal account as sent by AdminStaffMemberResource. */
export type AdminStaffMember = {
    id: number;
    name: string;
    email: string;
    role: 'staff' | 'admin';
    role_label: string;
    is_current_user: boolean;
    has_two_factor: boolean;
    added_on: string | null;
    is_active: boolean;
    deactivated_on: string | null;
    deactivated_by?: string | null;
    monthly_salary: number | null;
};

/** Lateness, absence and leave for a month, from SummariseAttendance. */
export type AttendanceSummary = {
    workingDays: number;
    lateDays: { date: string; label: string; startedAt: string }[];
    absentDays: { date: string; label: string }[];
    leaveDays: { date: string; label: string; type: string }[];
    holidays: { date: string; label: string; name: string }[];
};

export type AttendanceCounts = {
    workingDays: number;
    lateDays: number;
    absentDays: number;
    leaveDays: number;
};

export type LeaveStatusValue =
    | 'pending'
    | 'approved'
    | 'declined'
    | 'cancelled';

/** A leave request as sent by LeaveRequestResource. */
export type LeaveRequest = {
    id: number;
    type: string;
    type_label: string;
    starts_on: string;
    ends_on: string;
    dates: string;
    calendar_days: number;
    reason: string | null;
    status: LeaveStatusValue;
    status_label: string;
    decision_note: string | null;
    decided_by: string | null;
    requested_at: string | null;
    staff_name: string | null;
    staff_id: number;
};

export type PayrollStatusValue = 'pending' | 'approved' | 'paid';

/** One staff member's pay for a month, as sent by PayrollController. */
export type PayrollRow = {
    id: number;
    name: string;
    email: string;
    timesheetStatus: TimesheetStatusValue;
    timesheetLabel: string;
    attendance: AttendanceCounts;
    monthlySalary: string | null;
    adjustment: number | null;
    adjustmentLabel: string | null;
    adjustmentNote: string | null;
    approvedPay: string | null;
    status: PayrollStatusValue;
    statusLabel: string;
    approvedBy: string | null;
    approvedOn: string | null;
    paidBy: string | null;
    paidOn: string | null;
    approvalBlockers: string[];
};
