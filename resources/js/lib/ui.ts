import { cva } from 'class-variance-authority';

/** Small outlined buttons/links used in table rows (View, Edit, Delete...). */
export const rowActionVariants = cva(
    'inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            tone: {
                neutral: 'border-border bg-background text-foreground hover:bg-muted',
                primary: 'border-primary/30 bg-primary/5 text-primary hover:bg-primary hover:text-primary-foreground',
                success: 'border-success/30 bg-success/5 text-success hover:bg-success hover:text-success-foreground',
                warning: 'border-warning/30 bg-warning/5 text-warning hover:bg-warning hover:text-warning-foreground',
                danger: 'border-destructive/30 bg-destructive/5 text-destructive hover:bg-destructive hover:text-white',
            },
        },
        defaultVariants: { tone: 'neutral' },
    },
);

/** Shared table styling so every list page looks the same. */
export const table = {
    wrapper: 'overflow-hidden rounded-xl border border-border bg-card shadow-xs',
    toolbar: 'flex flex-wrap items-center gap-3 border-b border-border p-4',
    head: 'border-b border-border bg-muted/50 text-xs font-medium uppercase tracking-wide text-muted-foreground',
    th: 'px-4 py-3 font-medium',
    row: 'transition-colors hover:bg-muted/40',
    empty: 'px-4 py-12 text-center text-sm text-muted-foreground',
};

/** Native inputs/selects styled like the shadcn Input component. */
export const fieldClass =
    'h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
