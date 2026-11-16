import type { VariantProps } from 'class-variance-authority';
import { cva } from 'class-variance-authority';

export const siteButtonVariants = cva(
    'inline-flex max-w-full items-center justify-center gap-2 text-center leading-tight transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-60 [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                primary:
                    'rounded-[3px] bg-brand-700 font-display font-bold tracking-[0.06em] text-white uppercase hover:bg-brand-800 focus-visible:ring-brand-600',
                donate: 'rounded-[3px] bg-sun-400 font-display font-bold tracking-[0.06em] text-brand-950 uppercase hover:bg-sun-300 focus-visible:ring-sun-500',
                outline:
                    'rounded-[3px] border-2 border-brand-700 font-display font-bold tracking-[0.06em] text-brand-800 uppercase hover:bg-brand-700 hover:text-white focus-visible:ring-brand-600',
                light: 'rounded-[3px] bg-white font-display font-bold tracking-[0.06em] text-brand-900 uppercase hover:bg-cream focus-visible:ring-white focus-visible:ring-offset-brand-900',
                ghostLight:
                    'rounded-[3px] border-2 border-white/70 font-display font-bold tracking-[0.06em] text-white uppercase hover:border-white hover:bg-white hover:text-brand-900 focus-visible:ring-white focus-visible:ring-offset-brand-900',
                link: 'font-body font-bold text-brand-700 underline-offset-4 hover:text-brand-900 hover:underline focus-visible:ring-brand-600 [&_svg]:text-sun-500',
                linkLight:
                    'font-body font-bold text-white underline-offset-4 hover:underline focus-visible:ring-white [&_svg]:text-sun-400',
            },
            size: {
                sm: 'min-h-10 px-4 py-2 text-[0.95rem] [&_svg]:size-4',
                md: 'min-h-12 px-6 py-2.5 text-base [&_svg]:size-4',
                lg: 'min-h-14 px-7 py-3 text-lg [&_svg]:size-5',
            },
        },
        compoundVariants: [
            {
                variant: ['link', 'linkLight'],
                class: 'min-h-0 px-0 py-0 text-left text-[1rem] [&_svg]:size-4',
            },
        ],
        defaultVariants: {
            variant: 'primary',
            size: 'md',
        },
    },
);

export type SiteButtonVariants = VariantProps<typeof siteButtonVariants>;
