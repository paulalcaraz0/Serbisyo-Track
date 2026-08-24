import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type AdminHoliday } from '@/types/admin';
import { useForm } from '@inertiajs/react';
import { CalendarDays, Save } from 'lucide-react';
import { type FormEvent } from 'react';

interface Props {
    holiday?: AdminHoliday;
}

export default function HolidayForm({ holiday }: Props) {
    const form = useForm({
        date: holiday?.date ?? '',
        name_en: holiday?.name_en ?? '',
        name_fil: holiday?.name_fil ?? '',
        is_recurring: holiday?.is_recurring ?? false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (holiday) {
            form.put(route('admin.holidays.update', holiday.id), { preserveScroll: true });
        } else {
            form.post(route('admin.holidays.store'), { preserveScroll: true });
        }
    };

    return (
        <form onSubmit={submit} className="bg-card max-w-3xl space-y-6 rounded-xl border p-5 sm:p-7">
            <div className="grid gap-5 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="date">Date</Label>
                    <Input id="date" type="date" value={form.data.date} onChange={(event) => form.setData('date', event.target.value)} required />
                    <InputError message={form.errors.date} />
                    <p className="text-muted-foreground text-xs leading-5">Requests due after this date skip it as a non-working day.</p>
                </div>
                <div className="flex items-end">
                    <div className="space-y-2">
                        <label className="flex min-h-11 items-center gap-3 text-sm font-semibold" htmlFor="is_recurring">
                            <Checkbox
                                id="is_recurring"
                                checked={form.data.is_recurring}
                                onCheckedChange={(checked) => form.setData('is_recurring', checked === true)}
                            />
                            Repeats every year
                        </label>
                        <InputError message={form.errors.is_recurring} />
                        <p className="text-muted-foreground max-w-sm text-xs leading-5">
                            Use this for fixed-date observances such as New Year&rsquo;s Day. Leave unchecked for one-time or floating dates.
                        </p>
                    </div>
                </div>
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="name_en">Name (English)</Label>
                    <Input
                        id="name_en"
                        value={form.data.name_en}
                        onChange={(event) => form.setData('name_en', event.target.value)}
                        maxLength={150}
                        required
                    />
                    <InputError message={form.errors.name_en} />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="name_fil">Name (Filipino)</Label>
                    <Input
                        id="name_fil"
                        value={form.data.name_fil}
                        onChange={(event) => form.setData('name_fil', event.target.value)}
                        maxLength={150}
                        required
                    />
                    <InputError message={form.errors.name_fil} />
                </div>
            </div>

            <div className="flex justify-end border-t pt-6">
                <Button type="submit" disabled={form.processing}>
                    {holiday ? <Save /> : <CalendarDays />}
                    {form.processing ? 'Saving…' : holiday ? 'Save changes' : 'Add holiday'}
                </Button>
            </div>
        </form>
    );
}
