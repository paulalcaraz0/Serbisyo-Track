export interface AdminRequirement {
    [key: string]: string | boolean | null;

    name_en: string;
    name_fil: string;
    details_en: string | null;
    details_fil: string | null;
    is_required: boolean;
}

export interface AdminService {
    slug: string;
    name_en: string;
    name_fil: string;
    description_en: string;
    description_fil: string;
    eligibility_en: string;
    eligibility_fil: string;
    fee_centavos: number;
    processing_time_en: string;
    processing_time_fil: string;
    office_hours_en: string;
    office_hours_fil: string;
    procedure_steps_en: string[];
    procedure_steps_fil: string[];
    appointment_required: boolean;
    contact_email: string | null;
    contact_phone: string | null;
    is_active: boolean;
    archived_at: string | null;
    status: 'active' | 'inactive' | 'archived';
    requirements: AdminRequirement[];
    updated_at: string | null;
}
