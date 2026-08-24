import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import OfficeBanner from '@/components/office-banner';
import { PageAnnouncer } from '@/components/page-announcer';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export default function AppSidebarLayout({ children, breadcrumbs = [] }: { children: React.ReactNode; breadcrumbs?: BreadcrumbItem[] }) {
    const { translations } = usePage<SharedData>().props;

    return (
        <AppShell variant="sidebar">
            <PageAnnouncer />
            <a href="#main-content" className="skip-link">
                {translations.common.skip_to_content}
            </a>
            <OfficeBanner />
            <AppSidebar />
            <AppContent id="main-content" tabIndex={-1} variant="sidebar">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
