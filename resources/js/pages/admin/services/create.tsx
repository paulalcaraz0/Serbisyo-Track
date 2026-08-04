import { ServiceForm } from '@/components/admin/service-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Services', href: '/admin/services' },
    { title: 'Create', href: '/admin/services/create' },
];

export default function CreateService() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create service" />
            <div className="mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">
                <h1 className="text-2xl font-bold tracking-tight">Create service</h1>
                <p className="text-muted-foreground mt-2 text-sm leading-6">Publish complete English and Filipino guidance for residents.</p>
                <div className="mt-6">
                    <ServiceForm />
                </div>
            </div>
        </AppLayout>
    );
}
