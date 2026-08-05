import StaffForm from '@/components/admin/staff-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type StaffAccount } from '@/types/admin';
import { Head, usePage } from '@inertiajs/react';

interface Props {
    staffAccount: { data: StaffAccount };
}

export default function EditStaff({ staffAccount: resource }: Props) {
    const { flash } = usePage<SharedData>().props;
    const staff = resource.data;
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Staff', href: '/admin/staff' },
        { title: staff.name, href: `/admin/staff/${staff.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${staff.name}`} />
            <div className="p-4 sm:p-6 lg:p-8">
                <p className="text-primary text-sm font-semibold">Administrator</p>
                <h1 className="mt-1 text-2xl font-bold tracking-tight">Edit staff account</h1>
                <p className="text-muted-foreground mt-2 mb-6 text-sm">
                    {staff.open_assignments_count} currently open assignment{staff.open_assignments_count === 1 ? '' : 's'}.
                </p>
                {flash.success && (
                    <div className="mb-5 max-w-3xl rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">
                        {flash.success}
                    </div>
                )}
                <StaffForm staffAccount={staff} />
            </div>
        </AppLayout>
    );
}
