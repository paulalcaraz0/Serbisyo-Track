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
    translations: Translations;
    flash: {
        success?: string;
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
    };
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
