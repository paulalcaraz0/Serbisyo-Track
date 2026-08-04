import { ServiceForm } from '@/components/admin/service-form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type AdminService } from '@/types/services';
import { Head, Link } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';

export default function EditService({ service: resource }: { service: { data: AdminService } }) {
    const service = resource.data;
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Services', href: '/admin/services' },
        { title: service.name_en, href: `/admin/services/${service.slug}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${service.name_en}`} />
            <div className="mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-muted-foreground text-sm font-semibold">
                            {service.status === 'archived' ? 'Archived service' : 'Service administration'}
                        </p>
                        <h1 className="mt-1 text-2xl font-bold tracking-tight">Edit {service.name_en}</h1>
                    </div>
                    {service.status === 'active' && (
                        <Link
                            href={route('services.show', service.slug)}
                            className="hover:bg-accent inline-flex min-h-11 items-center gap-2 rounded-md border px-4 text-sm font-semibold"
                            target="_blank"
                        >
                            Public view
                            <ExternalLink className="size-4" aria-hidden="true" />
                        </Link>
                    )}
                </div>
                {service.status === 'archived' && (
                    <p role="status" className="mt-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950">
                        Restore this service from the service list before making it public again.
                    </p>
                )}
                <div className="mt-6">
                    <ServiceForm service={service} />
                </div>
            </div>
        </AppLayout>
    );
}
