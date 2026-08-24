import HolidayForm from '@/components/admin/holiday-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type AdminHoliday } from '@/types/admin';
import { Head } from '@inertiajs/react';

interface Props {
    holiday: { data: AdminHoliday };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Holiday calendar', href: '/admin/holidays' },
];

export default function HolidaysEdit({ holiday }: Props) {
    return (
        <AppLayout breadcrumbs={[...breadcrumbs, { title: `Edit ${holiday.data.name_en}`, href: `/admin/holidays/${holiday.data.id}/edit` }]}>
            <Head title="Edit holiday" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header>
                    <p className="text-primary text-sm font-semibold">Administrator</p>
                    <h1 className="mt-1 text-2xl font-bold tracking-tight">Edit holiday</h1>
                    <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                        Changes apply to future due-date calculations. Due dates already stored on requests remain unchanged.
                    </p>
                </header>
                <HolidayForm holiday={holiday.data} />
            </div>
        </AppLayout>
    );
}
