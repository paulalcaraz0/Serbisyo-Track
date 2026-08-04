import { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="2" y="2" width="36" height="36" rx="12" fill="currentColor" />
            <path
                d="M27.4 13.8c-1.7-1.5-4-2.3-6.7-2.3-3.7 0-6.5 1.8-6.5 4.7 0 3.2 2.7 4.1 6.1 4.8 2.7.6 3.8 1 3.8 2.4 0 1.3-1.4 2.1-3.4 2.1-2.4 0-4.6-.9-6.3-2.6"
                fill="none"
                stroke="white"
                strokeWidth="2.8"
                strokeLinecap="round"
            />
            <circle cx="29.2" cy="27.5" r="2.1" fill="#f4b860" />
        </svg>
    );
}
