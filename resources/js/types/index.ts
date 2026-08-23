import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User | null;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    auth: Auth;
    locale: string;
    supportedLocales: Record<string, string>;
    office: {
        name: string;
        address: string;
        email: string;
        phone: string;
    };
    translations: Translations;
    flash: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    role: 'staff' | 'administrator';
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface TranslatedFeature {
    title: string;
    body: string;
}

export interface InfoSection {
    title: string;
    body: string;
}

export interface InfoPageTranslation {
    meta_title: string;
    eyebrow: string;
    title: string;
    intro: string;
    sections: InfoSection[];
}

export interface Translations {
    errors: {
        meta_title: string;
        eyebrow: string;
        home: string;
        services: string;
        track: string;
        dashboard: string;
        help: string;
        statuses: Record<string, { title: string; description: string }>;
    };
    common: {
        language_label: string;
        english: string;
        filipino: string;
        staff_portal: string;
        dashboard: string;
        skip_to_content: string;
        home: string;
        services: string;
        privacy: string;
        accessibility: string;
        help: string;
        back: string;
        track: string;
    };
    services: {
        meta_title: string;
        eyebrow: string;
        title: string;
        intro: string;
        available_count: string;
        view_details: string;
        fee: string;
        free: string;
        processing_time: string;
        office_hours: string;
        appointment: string;
        appointment_required: string;
        appointment_not_required: string;
        empty_title: string;
        empty_body: string;
        back_to_services: string;
        eligibility: string;
        requirements: string;
        required: string;
        optional: string;
        procedure: string;
        contact: string;
        email: string;
        phone: string;
        next_step_title: string;
        next_step_body: string;
        start_request: string;
        track_request: string;
    };
    requests: {
        meta_title: string;
        eyebrow: string;
        title: string;
        intro: string;
        back_to_service: string;
        step_of: string;
        steps: { contact: string; details: string; review: string };
        demo_warning_title: string;
        demo_warning_body: string;
        contact_title: string;
        contact_intro: string;
        resident_name: string;
        resident_name_help: string;
        email: string;
        phone: string;
        preferred_contact: string;
        contact_by_email: string;
        contact_by_phone: string;
        general_location: string;
        general_location_help: string;
        details_title: string;
        details_intro: string;
        request_details: string;
        request_details_help: string;
        appointment_title: string;
        appointment_required: string;
        appointment_optional: string;
        request_appointment: string;
        appointment_date: string;
        appointment_time: string;
        morning: string;
        afternoon: string;
        appointment_note: string;
        appointment_help: string;
        attachments_title: string;
        attachments_help: string;
        selected_files: string;
        remove_file: string;
        review_title: string;
        review_intro: string;
        review_service: string;
        review_contact: string;
        review_details: string;
        review_appointment: string;
        review_attachments: string;
        none: string;
        privacy_consent: string;
        consent_help: string;
        continue: string;
        back: string;
        submit: string;
        submitting: string;
        error_title: string;
        receipt_meta_title: string;
        receipt_eyebrow: string;
        receipt_title: string;
        receipt_intro: string;
        reference: string;
        tracking_pin: string;
        pin_once_title: string;
        pin_once_body: string;
        pin_hidden: string;
        submitted: string;
        requested_schedule: string;
        receipt_files: string;
        print_receipt: string;
        view_status: string;
        another_service: string;
    };
    tracking: {
        meta_title: string;
        eyebrow: string;
        title: string;
        intro: string;
        reference: string;
        reference_placeholder: string;
        pin: string;
        submit: string;
        checking: string;
        privacy_note: string;
        invalid: string;
        access_expired: string;
        status_meta_title: string;
        status_eyebrow: string;
        status_title: string;
        service: string;
        submitted: string;
        last_updated: string;
        appointment: string;
        appointment_requested: string;
        appointment_note: string;
        attachments: string;
        download: string;
        no_attachments: string;
        track_another: string;
        receipt: string;
        history: string;
        history_intro: string;
        no_history: string;
    };
    statuses: Record<string, { label: string; description: string }>;
    appointment_statuses: Record<string, string>;
    info: {
        privacy: InfoPageTranslation;
        accessibility: InfoPageTranslation;
        help: InfoPageTranslation;
    };
    home: {
        meta_title: string;
        meta_description: string;
        disclaimer_label: string;
        disclaimer: string;
        eyebrow: string;
        title: string;
        description: string;
        primary_action: string;
        secondary_action: string;
        trust_note: string;
        preview_label: string;
        preview_title: string;
        preview_items: string[];
        stats_label: string;
        stats: { value: string; label: string }[];
        features_label: string;
        features_title: string;
        features_intro: string;
        features: TranslatedFeature[];
        steps_label: string;
        steps_title: string;
        steps: TranslatedFeature[];
        foundation_label: string;
        foundation_title: string;
        foundation_body: string;
        foundation_points: string[];
        footer_office: string;
        footer_phase: string;
    };
    auth: {
        title: string;
        description: string;
        email: string;
        password: string;
        remember: string;
        forgot: string;
        submit: string;
        restricted: string;
    };
}
