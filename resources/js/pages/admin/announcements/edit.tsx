import AnnouncementForm from '@/components/admin/announcement-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type AdminAnnouncement } from '@/types/admin';
import { Head } from '@inertiajs/react';

interface Props {
    announcement: { data: AdminAnnouncement };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Announcements', href: '/admin/announcements' },
];

export default function AnnouncementsEdit({ announcement }: Props) {
    return (
        <AppLayout breadcrumbs={[...breadcrumbs, { title: 'Edit announcement', href: `/admin/announcements/${announcement.data.id}/edit` }]}>
            <Head title="Edit announcement" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header>
                    <p className="text-primary text-sm font-semibold">Administrator</p>
                    <h1 className="mt-1 text-2xl font-bold tracking-tight">Edit announcement</h1>
                    <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                        Deactivate to hide a notice without deleting it, or adjust its visibility window.
                    </p>
                </header>
                <AnnouncementForm announcement={announcement.data} />
            </div>
        </AppLayout>
    );
}
