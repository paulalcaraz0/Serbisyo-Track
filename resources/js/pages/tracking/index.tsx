import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { LockKeyhole, Search, ShieldCheck } from 'lucide-react';
import { type FormEvent } from 'react';

interface TrackingFormData {
    [key: string]: string;
    reference: string;
    pin: string;
}

export default function TrackingIndex() {
    const { translations } = usePage<SharedData>().props;
    const copy = translations.tracking;
    const { data, setData, post, processing, errors } = useForm<TrackingFormData>({ reference: '', pin: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('tracking.verify'), { preserveScroll: true });
    };

    return (
        <PublicLayout>
            <Head title={copy.meta_title}>
                <meta name="robots" content="noindex,nofollow,noarchive" />
            </Head>
            <section className="border-b border-slate-200 bg-white">
                <div className="mx-auto max-w-3xl px-4 py-12 text-center sm:px-6 sm:py-16 lg:px-8">
                    <div className="mx-auto flex size-12 items-center justify-center rounded-2xl bg-[#e4f1ed] text-[#14594f]">
                        <Search className="size-6" aria-hidden="true" />
                    </div>
                    <p className="mt-5 text-xs font-bold tracking-[0.14em] text-[#14594f] uppercase">{copy.eyebrow}</p>
                    <h1 className="mt-2 text-4xl font-bold tracking-[-0.04em] sm:text-5xl">{copy.title}</h1>
                    <p className="mx-auto mt-5 max-w-2xl leading-7 text-slate-600">{copy.intro}</p>
                </div>
            </section>
            <div className="mx-auto max-w-xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                <form onSubmit={submit} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="reference">{copy.reference}</Label>
                            <Input
                                id="reference"
                                className="font-mono uppercase"
                                value={data.reference}
                                onChange={(e) => setData('reference', e.target.value.toUpperCase())}
                                placeholder={copy.reference_placeholder}
                                maxLength={20}
                                autoCapitalize="characters"
                                autoComplete="off"
                            />
                            <InputError message={errors.reference} />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="pin">{copy.pin}</Label>
                            <Input
                                id="pin"
                                className="font-mono tracking-[0.2em]"
                                type="password"
                                inputMode="numeric"
                                pattern="[0-9]*"
                                value={data.pin}
                                onChange={(e) => setData('pin', e.target.value.replace(/\D/g, '').slice(0, 6))}
                                maxLength={6}
                                autoComplete="off"
                            />
                            <InputError message={errors.pin} />
                        </div>
                    </div>
                    <Button type="submit" size="lg" className="mt-7 min-h-12 w-full" disabled={processing}>
                        <LockKeyhole />
                        {processing ? copy.checking : copy.submit}
                    </Button>
                </form>
                <div className="mt-5 flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-5 text-sm leading-6 text-slate-600">
                    <ShieldCheck className="mt-0.5 size-5 shrink-0 text-[#14594f]" aria-hidden="true" />
                    <p>{copy.privacy_note}</p>
                </div>
            </div>
        </PublicLayout>
    );
}
