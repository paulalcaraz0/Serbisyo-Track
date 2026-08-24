import AnnouncementForm from '@/components/admin/announcement-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Announcements', href: '/admin/announcements' },
    { title: 'New announcement', href: '/admin/announcements/create' },
];

export default function AnnouncementsCreate() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New announcement" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header>
                    <p className="text-primary text-sm font-semibold">Administrator</p>
                    <h1 className="mt-1 text-2xl font-bold tracking-tight">Publish an announcement</h1>
                    <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                        Write the notice in both languages, choose a severity, and set how long it stays visible.
                    </p>
                </header>
                <AnnouncementForm />
            </div>
        </AppLayout>
    );
}
