import { cn } from '@/lib/utils';
import { type ImgHTMLAttributes } from 'react';

export default function AppLogoIcon({ alt = '', className, ...props }: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            {...props}
            src="/branding/serbisyo-track-icon.png"
            alt={alt}
            aria-hidden={alt === '' ? true : undefined}
            className={cn('shrink-0 scale-[1.45] object-contain', className)}
            decoding="async"
        />
    );
}
