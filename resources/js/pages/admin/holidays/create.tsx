import HolidayForm from '@/components/admin/holiday-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Holiday calendar', href: '/admin/holidays' },
    { title: 'Add holiday', href: '/admin/holidays/create' },
];

export default function HolidaysCreate() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add holiday" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header>
                    <p className="text-primary text-sm font-semibold">Administrator</p>
                    <h1 className="mt-1 text-2xl font-bold tracking-tight">Add a non-working day</h1>
                    <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                        Declared holidays are excluded from service due-date targets for all new requests.
                    </p>
                </header>
                <HolidayForm />
            </div>
        </AppLayout>
    );
}
