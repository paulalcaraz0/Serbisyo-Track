import StaffForm from '@/components/admin/staff-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/admin/staff' },
    { title: 'Create', href: '/admin/staff/create' },
];

export default function CreateStaff() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create staff account" />
            <div className="p-4 sm:p-6 lg:p-8">
                <p className="text-primary text-sm font-semibold">Administrator</p>
                <h1 className="mt-1 text-2xl font-bold tracking-tight">Create staff account</h1>
                <p className="text-muted-foreground mt-2 mb-6 max-w-2xl text-sm leading-6">
                    Create fictional or authorized internal accounts only. Public registration remains disabled.
                </p>
                <StaffForm />
            </div>
        </AppLayout>
    );
}
