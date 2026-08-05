import { cn } from '@/lib/utils';
import { HTMLAttributes } from 'react';

export default function InputError({ message, className = '', ...props }: HTMLAttributes<HTMLParagraphElement> & { message?: string }) {
    return message ? (
        <p {...props} role="alert" aria-live="polite" className={cn('text-sm font-medium text-red-700 dark:text-red-300', className)}>
            {message}
        </p>
    ) : null;
}
