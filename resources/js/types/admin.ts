export interface StaffAccount {
    id: number;
    name: string;
    email: string;
    role: 'staff' | 'administrator';
    role_label: string;
    is_active: boolean;
    is_verified: boolean;
    last_login_at: string | null;
    created_at: string | null;
    open_assignments_count: number;
}

export interface OfficeSettings {
    office_name_en: string;
    office_name_fil: string;
    contact_email: string;
    contact_phone: string;
    address_en: string;
    address_fil: string;
    retention_days: number;
    updated_at: string | null;
}

export interface AuditEvent {
    id: number;
    action: string;
    action_label: string;
    actor: { id: number; name: string } | null;
    subject_type: string;
    subject_identifier: string | null;
    metadata: Record<string, string | number | boolean | null>;
    created_at: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
