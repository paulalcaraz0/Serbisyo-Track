export interface StaffRequestSummary {
    reference: string;
    service: { slug: string; name: string };
    status: string;
    status_label: string;
    assignee: { id: number; name: string } | null;
    submitted_at: string;
    due_at: string | null;
    is_overdue: boolean;
    has_appointment: boolean;
    updated_at: string;
}

export interface RequestActivity {
    id: number;
    event_type: 'submitted' | 'assignment' | 'status_change' | 'internal_note' | 'appointment';
    actor: string | null;
    subject_user: string | null;
    from_status: string | null;
    to_status: string | null;
    public_message_en: string | null;
    public_message_fil: string | null;
    private_details: string | null;
    created_at: string;
}

export interface StaffRequestRecord {
    reference: string;
    service: { slug: string; name: string; processing_time: string; target_business_days: number };
    status: string;
    status_label: string;
    resident: {
        name: string;
        email: string | null;
        phone: string | null;
        preferred_contact: 'email' | 'phone';
        general_location: string | null;
    };
    request_details: string;
    consented_at: string;
    submitted_at: string;
    due_at: string | null;
    closed_at: string | null;
    is_overdue: boolean;
    assignee: { id: number; name: string } | null;
    appointment: {
        preferred_date: string;
        preferred_time_window: 'morning' | 'afternoon';
        resident_note: string | null;
        status: string;
        confirmed_start_at: string | null;
    } | null;
    attachments: { public_id: string; name: string; mime_type: string; size_bytes: number }[];
    activities: RequestActivity[];
    permissions: { assign: boolean; transition: boolean; add_note: boolean; manage_appointment: boolean };
}
