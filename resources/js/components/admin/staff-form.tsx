import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type StaffAccount } from '@/types/admin';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, UserPlus } from 'lucide-react';
import { type FormEvent } from 'react';

interface Props {
    staffAccount?: StaffAccount;
}

export default function StaffForm({ staffAccount }: Props) {
    const editing = staffAccount !== undefined;
    const form = useForm({
        name: staffAccount?.name ?? '',
        email: staffAccount?.email ?? '',
        role: staffAccount?.role ?? 'staff',
        is_active: staffAccount?.is_active ?? true,
        password: '',
        password_confirmation: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (staffAccount) {
            form.put(route('admin.staff.update', staffAccount.id), { preserveScroll: true });
        } else {
            form.post(route('admin.staff.store'));
        }
    };

    return (
        <form onSubmit={submit} className="bg-card max-w-3xl space-y-7 rounded-xl border p-5 sm:p-7">
            <div className="grid gap-6 sm:grid-cols-2">
                <div className="space-y-2 sm:col-span-2">
                    <Label htmlFor="name">Full name</Label>
                    <Input id="name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} maxLength={100} />
                    <InputError message={form.errors.name} />
                </div>
                <div className="space-y-2 sm:col-span-2">
                    <Label htmlFor="email">Work email</Label>
                    <Input
                        id="email"
                        type="email"
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                        maxLength={150}
                        autoComplete="email"
                    />
                    <InputError message={form.errors.email} />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="role">Role</Label>
                    <select
                        id="role"
                        className="bg-background h-10 w-full rounded-md border px-3 text-sm"
                        value={form.data.role}
                        onChange={(event) => form.setData('role', event.target.value as 'staff' | 'administrator')}
                    >
                        <option value="staff">Staff</option>
                        <option value="administrator">Administrator</option>
                    </select>
                    <InputError message={form.errors.role} />
                </div>
                {editing && (
                    <div className="space-y-2">
                        <span className="text-sm font-semibold">Account access</span>
                        <label className="flex min-h-10 items-center gap-3 rounded-md border px-3 text-sm font-semibold">
                            <input
                                type="checkbox"
                                checked={form.data.is_active}
                                onChange={(event) => form.setData('is_active', event.target.checked)}
                                className="size-4"
                            />
                            Active account
                        </label>
                        <InputError message={form.errors.is_active} />
                    </div>
                )}
                <div className="space-y-2">
                    <Label htmlFor="password">{editing ? 'New password (optional)' : 'Temporary password'}</Label>
                    <Input
                        id="password"
                        type="password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        autoComplete="new-password"
                    />
                    <InputError message={form.errors.password} />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)}
                        autoComplete="new-password"
                    />
                </div>
            </div>

            <div className="bg-muted/60 rounded-lg border p-4 text-sm leading-6">
                Passwords require at least 12 characters with uppercase, lowercase, number, and symbol. New accounts are verified by the creating
                administrator. Deactivated users are signed out and their open assignments are released.
            </div>

            <div className="flex flex-wrap items-center justify-between gap-3 border-t pt-6">
                <Link
                    href={route('admin.staff.index')}
                    className="text-primary inline-flex min-h-10 items-center gap-2 text-sm font-semibold hover:underline"
                >
                    <ArrowLeft className="size-4" />
                    Back to staff
                </Link>
                <Button type="submit" disabled={form.processing}>
                    {editing ? <Save /> : <UserPlus />}
                    {form.processing ? 'Saving…' : editing ? 'Save account' : 'Create account'}
                </Button>
            </div>
        </form>
    );
}
