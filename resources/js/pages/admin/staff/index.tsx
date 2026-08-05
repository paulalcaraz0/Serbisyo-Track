import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type PaginationLink, type StaffAccount } from '@/types/admin';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Edit3, Search, UserPlus, Users } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface Props {
    staffAccounts: { data: StaffAccount[]; links: PaginationLink[]; meta: { from: number | null; to: number | null; total: number } };
    filters: { search: string; role: string; status: string };
    summary: { active: number; inactive: number; administrators: number };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/admin/staff' },
];

export default function StaffIndex({ staffAccounts, filters, summary }: Props) {
    const { auth, flash } = usePage<SharedData>().props;
    const [search, setSearch] = useState(filters.search);
    const [role, setRole] = useState(filters.role);
    const [status, setStatus] = useState(filters.status);

    const applyFilters = (event: FormEvent) => {
        event.preventDefault();
        router.get(route('admin.staff.index'), { search, role, status }, { preserveState: true, replace: true });
    };

    const formatDate = (value: string | null) =>
        value ? new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : 'Never';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Staff administration" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p className="text-primary text-sm font-semibold">Administrator</p>
                        <h1 className="mt-1 text-2xl font-bold tracking-tight">Staff accounts</h1>
                        <p className="text-muted-foreground mt-2 max-w-3xl text-sm leading-6">
                            Create, review, and deactivate staff access without deleting operational history.
                        </p>
                    </div>
                    <Link href={route('admin.staff.create')} className={buttonVariants({ size: 'lg' })}>
                        <UserPlus />
                        Add staff account
                    </Link>
                </header>

                {flash.success && (
                    <div className="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">
                        {flash.success}
                    </div>
                )}

                <section className="grid gap-3 sm:grid-cols-3" aria-label="Staff totals">
                    {[
                        ['Active', summary.active],
                        ['Inactive', summary.inactive],
                        ['Active administrators', summary.administrators],
                    ].map(([label, value]) => (
                        <div key={label} className="bg-card rounded-xl border p-4">
                            <p className="text-muted-foreground text-sm font-semibold">{label}</p>
                            <p className="mt-1 text-3xl font-bold">{value}</p>
                        </div>
                    ))}
                </section>

                <form onSubmit={applyFilters} className="bg-card grid gap-3 rounded-xl border p-4 md:grid-cols-[minmax(14rem,1fr)_11rem_11rem_auto]">
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Search</span>
                        <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Name or work email" maxLength={100} />
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Role</span>
                        <select
                            className="bg-background h-10 w-full rounded-md border px-3"
                            value={role}
                            onChange={(event) => setRole(event.target.value)}
                        >
                            <option value="all">All roles</option>
                            <option value="staff">Staff</option>
                            <option value="administrator">Administrators</option>
                        </select>
                    </label>
                    <label className="space-y-1 text-sm font-semibold">
                        <span>Status</span>
                        <select
                            className="bg-background h-10 w-full rounded-md border px-3"
                            value={status}
                            onChange={(event) => setStatus(event.target.value)}
                        >
                            <option value="all">All accounts</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </label>
                    <Button type="submit" className="self-end">
                        <Search />
                        Apply
                    </Button>
                </form>

                <section className="bg-card overflow-hidden rounded-xl border" aria-labelledby="staff-results">
                    <div className="flex items-center gap-3 border-b px-4 py-3">
                        <Users className="text-primary size-5" />
                        <div>
                            <h2 id="staff-results" className="font-bold">
                                Accounts
                            </h2>
                            <p className="text-muted-foreground text-sm">
                                Showing {staffAccounts.meta.from ?? 0}–{staffAccounts.meta.to ?? 0} of {staffAccounts.meta.total}
                            </p>
                        </div>
                    </div>
                    {staffAccounts.data.length === 0 ? (
                        <div className="p-8 text-center text-sm">No staff accounts match these filters.</div>
                    ) : (
                        <div className="divide-y">
                            {staffAccounts.data.map((staff) => (
                                <article
                                    key={staff.id}
                                    className="grid gap-4 p-4 lg:grid-cols-[minmax(14rem,1fr)_9rem_10rem_13rem_auto] lg:items-center"
                                >
                                    <div>
                                        <h3 className="font-bold">
                                            {staff.name}
                                            {staff.id === auth.user?.id ? ' (you)' : ''}
                                        </h3>
                                        <p className="text-muted-foreground mt-1 text-sm">{staff.email}</p>
                                    </div>
                                    <span className="bg-secondary text-secondary-foreground w-fit rounded-full px-3 py-1 text-xs font-bold">
                                        {staff.role_label}
                                    </span>
                                    <span
                                        className={`w-fit rounded-full px-3 py-1 text-xs font-bold ${staff.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'}`}
                                    >
                                        {staff.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                    <div className="text-muted-foreground text-sm">
                                        <p>
                                            {staff.open_assignments_count} open assignment{staff.open_assignments_count === 1 ? '' : 's'}
                                        </p>
                                        <p className="mt-1 text-xs">Last login: {formatDate(staff.last_login_at)}</p>
                                    </div>
                                    <Link href={route('admin.staff.edit', staff.id)} className={buttonVariants({ variant: 'outline', size: 'sm' })}>
                                        <Edit3 />
                                        Edit
                                    </Link>
                                </article>
                            ))}
                        </div>
                    )}
                    {staffAccounts.links.length > 3 && (
                        <nav aria-label="Staff account pages" className="flex flex-wrap gap-2 border-t p-4">
                            {staffAccounts.links.map((link, index) =>
                                link.url ? (
                                    <Link
                                        key={`${link.label}-${index}`}
                                        href={link.url}
                                        preserveScroll
                                        className={cn(buttonVariants({ variant: link.active ? 'default' : 'outline', size: 'sm' }), 'min-w-10')}
                                    >
                                        {link.label.replace('&laquo;', '‹').replace('&raquo;', '›')}
                                    </Link>
                                ) : (
                                    <span
                                        key={`${link.label}-${index}`}
                                        className={cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'min-w-10 opacity-50')}
                                        aria-disabled="true"
                                    >
                                        {link.label.replace('&laquo;', '‹').replace('&raquo;', '›')}
                                    </span>
                                ),
                            )}
                        </nav>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
