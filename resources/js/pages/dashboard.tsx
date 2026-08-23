import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { BarChart3, ClipboardCheck, ClipboardList, LibraryBig, ShieldCheck, Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    const { auth } = usePage<SharedData>().props;
    const user = auth.user!;
    const roleLabel = user.role === 'administrator' ? 'Administrator' : 'Staff';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Staff dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div className="bg-card rounded-2xl border p-6 shadow-sm">
                    <p className="text-primary text-sm font-semibold">Phase 5 workspace</p>
                    <h1 className="mt-2 text-2xl font-bold tracking-tight">Welcome, {user.name}</h1>
                    <p className="text-muted-foreground mt-2 max-w-3xl text-sm leading-6">
                        Your {roleLabel.toLowerCase()} account is active. Request operations are available to all staff, with reporting, staff
                        administration, settings, exports, and audit history available to administrators.
                    </p>
                    <div className="mt-5 flex flex-wrap gap-3">
                        <Link
                            href={route('staff.requests.index')}
                            className="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex min-h-11 items-center gap-2 rounded-lg px-4 text-sm font-bold"
                        >
                            <ClipboardList className="size-4" aria-hidden="true" />
                            Open request queue
                        </Link>
                        {user.role === 'administrator' && (
                            <>
                                <Link
                                    href={route('admin.reports.index')}
                                    className="bg-card hover:bg-muted inline-flex min-h-11 items-center gap-2 rounded-lg border px-4 text-sm font-bold"
                                >
                                    <BarChart3 className="size-4" aria-hidden="true" />
                                    View reports
                                </Link>
                                <Link
                                    href={route('admin.staff.index')}
                                    className="bg-card hover:bg-muted inline-flex min-h-11 items-center gap-2 rounded-lg border px-4 text-sm font-bold"
                                >
                                    <Users className="size-4" aria-hidden="true" />
                                    Manage staff
                                </Link>
                                <Link
                                    href={route('admin.services.index')}
                                    className="bg-card hover:bg-muted inline-flex min-h-11 items-center gap-2 rounded-lg border px-4 text-sm font-bold"
                                >
                                    <LibraryBig className="size-4" aria-hidden="true" />
                                    Manage services
                                </Link>
                            </>
                        )}
                    </div>
                </div>

                <section aria-labelledby="foundation-capabilities">
                    <h2 id="foundation-capabilities" className="text-lg font-bold">
                        Verified foundation capabilities
                    </h2>
                    <div className="mt-4 grid gap-4 md:grid-cols-3">
                        {[
                            {
                                icon: ShieldCheck,
                                title: 'Protected access',
                                body: 'Authentication is rate limited and inactive accounts are rejected.',
                            },
                            {
                                icon: ClipboardCheck,
                                title: 'Server authorization',
                                body: 'Staff routes require an active, verified account on the server.',
                            },
                            {
                                icon: BarChart3,
                                title: 'Dashboard-ready',
                                body: 'Aggregate reporting and sanitized exports exclude resident-submitted personal details.',
                            },
                        ].map((item) => (
                            <article key={item.title} className="bg-card rounded-2xl border p-5">
                                <item.icon className="text-primary size-6" aria-hidden="true" />
                                <h3 className="mt-4 font-bold">{item.title}</h3>
                                <p className="text-muted-foreground mt-2 text-sm leading-6">{item.body}</p>
                            </article>
                        ))}
                    </div>
                </section>

                <div className="bg-muted/60 rounded-xl border px-5 py-4 text-sm">
                    Signed in as <strong>{user.email}</strong> · Role: <strong>{roleLabel}</strong>
                </div>
            </div>
        </AppLayout>
    );
}
