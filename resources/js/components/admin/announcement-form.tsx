import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type AdminAnnouncement } from '@/types/admin';
import { useForm } from '@inertiajs/react';
import { Megaphone, Save } from 'lucide-react';
import { type FormEvent } from 'react';

interface Props {
    announcement?: AdminAnnouncement;
}

const levels: { value: AdminAnnouncement['level']; label: string; hint: string }[] = [
    { value: 'info', label: 'Information', hint: 'General notice shown in blue.' },
    { value: 'warning', label: 'Warning', hint: 'Disruption notice, such as a counter closure, shown in amber.' },
    { value: 'critical', label: 'Critical', hint: 'Urgent suspension or emergency notice shown in red and announced assertively.' },
];

const toLocalInput = (value: string | null): string => (value ? value.slice(0, 16) : '');

export default function AnnouncementForm({ announcement }: Props) {
    const form = useForm({
        message_en: announcement?.message_en ?? '',
        message_fil: announcement?.message_fil ?? '',
        level: announcement?.level ?? ('info' as AdminAnnouncement['level']),
        starts_at: toLocalInput(announcement?.starts_at ?? null),
        ends_at: toLocalInput(announcement?.ends_at ?? null),
        is_active: announcement?.is_active ?? true,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (announcement) {
            form.put(route('admin.announcements.update', announcement.id), { preserveScroll: true });
        } else {
            form.post(route('admin.announcements.store'), { preserveScroll: true });
        }
    };

    return (
        <form onSubmit={submit} className="bg-card max-w-3xl space-y-6 rounded-xl border p-5 sm:p-7">
            <div className="grid gap-5 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="message_en">Message (English)</Label>
                    <Input
                        id="message_en"
                        value={form.data.message_en}
                        onChange={(event) => form.setData('message_en', event.target.value)}
                        maxLength={500}
                        required
                    />
                    <InputError message={form.errors.message_en} />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="message_fil">Message (Filipino)</Label>
                    <Input
                        id="message_fil"
                        value={form.data.message_fil}
                        onChange={(event) => form.setData('message_fil', event.target.value)}
                        maxLength={500}
                        required
                    />
                    <InputError message={form.errors.message_fil} />
                </div>
            </div>

            <fieldset>
                <legend className="text-sm font-semibold">Severity</legend>
                <div className="mt-3 grid gap-3 sm:grid-cols-3">
                    {levels.map((level) => (
                        <label
                            key={level.value}
                            htmlFor={`level-${level.value}`}
                            className={`focus-within:ring-primary cursor-pointer rounded-xl border p-4 text-sm ${
                                form.data.level === level.value ? 'border-primary bg-secondary/50' : 'hover:bg-muted/40'
                            }`}
                        >
                            <span className="flex items-center gap-2 font-bold">
                                <input
                                    id={`level-${level.value}`}
                                    type="radio"
                                    name="level"
                                    value={level.value}
                                    checked={form.data.level === level.value}
                                    onChange={() => form.setData('level', level.value)}
                                    className="accent-primary size-4"
                                />
                                {level.label}
                            </span>
                            <span className="text-muted-foreground mt-1 block text-xs leading-5">{level.hint}</span>
                        </label>
                    ))}
                </div>
                <InputError message={form.errors.level} />
            </fieldset>

            <div className="grid gap-5 border-t pt-6 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="starts_at">Visible from</Label>
                    <Input
                        id="starts_at"
                        type="datetime-local"
                        value={form.data.starts_at}
                        onChange={(event) => form.setData('starts_at', event.target.value)}
                    />
                    <InputError message={form.errors.starts_at} />
                    <p className="text-muted-foreground text-xs">Leave empty to show immediately.</p>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="ends_at">Visible until</Label>
                    <Input
                        id="ends_at"
                        type="datetime-local"
                        value={form.data.ends_at}
                        onChange={(event) => form.setData('ends_at', event.target.value)}
                    />
                    <InputError message={form.errors.ends_at} />
                    <p className="text-muted-foreground text-xs">Leave empty to show until removed or deactivated.</p>
                </div>
            </div>

            <div className="space-y-2 border-t pt-6">
                <label className="flex min-h-11 items-center gap-3 text-sm font-semibold" htmlFor="is_active">
                    <Checkbox
                        id="is_active"
                        checked={form.data.is_active}
                        onCheckedChange={(checked) => form.setData('is_active', checked === true)}
                    />
                    Active
                </label>
                <InputError message={form.errors.is_active} />
                <p className="text-muted-foreground text-xs">Inactive announcements are kept for reuse but never displayed.</p>
            </div>

            <div className="flex justify-end border-t pt-6">
                <Button type="submit" disabled={form.processing}>
                    {announcement ? <Save /> : <Megaphone />}
                    {form.processing ? 'Saving…' : announcement ? 'Save changes' : 'Publish announcement'}
                </Button>
            </div>
        </form>
    );
}
