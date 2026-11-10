import { cva } from 'class-variance-authority';

/** Small outlined buttons/links used in table rows (View, Edit, Delete...). */
export const rowActionVariants = cva(
    'focus-visible:ring-ring/50 inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium transition-colors focus-visible:ring-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            tone: {
                neutral:
                    'border-border bg-background text-foreground hover:bg-muted',
                primary:
                    'border-primary/30 bg-primary/5 text-primary hover:bg-primary hover:text-primary-foreground',
                success:
                    'border-success/30 bg-success/5 text-success hover:bg-success hover:text-success-foreground',
                warning:
                    'border-warning/30 bg-warning/5 text-warning hover:bg-warning hover:text-warning-foreground',
                // Quiet until hovered: deleting is the rarest action, so it shouldn't shout on every row.
                danger: 'border-border bg-background text-muted-foreground hover:border-destructive/40 hover:bg-destructive/10 hover:text-destructive',
            },
        },
        defaultVariants: { tone: 'neutral' },
    },
);

/** Shared table styling so every list page looks the same. */
export const table = {
    wrapper:
        'overflow-hidden rounded-xl border border-border bg-card shadow-xs',
    toolbar: 'flex flex-wrap items-center gap-3 border-b border-border p-4',
    // Quiet header: sentence case, no fill, so the rows carry the weight.
    head: 'border-b border-border text-xs font-medium text-muted-foreground [&_th]:h-11 [&_th]:font-medium',
    th: 'px-4 py-3 font-medium',
    row: 'transition-colors hover:bg-muted/40',
    /** Rows that open the record when clicked. */
    clickableRow: 'cursor-pointer transition-colors hover:bg-muted/40',
    cell: 'px-4 py-3.5 align-middle',
    empty: 'px-4 py-12 text-center text-sm text-muted-foreground',
};

/** Native inputs/selects styled like the shadcn Input component. */
export const fieldClass =
    'h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50';

/** Full-width variant for form pages. */
export const selectClass = `${fieldClass} w-full`;

/** Multi-line text, matching the other fields. */
export const textareaClass =
    'w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

/** Card used for sections on detail and form pages. */
export const card = {
    base: 'overflow-hidden rounded-xl border border-border bg-card shadow-xs',
    header: 'flex items-center justify-between gap-3 border-b border-border px-5 py-4',
    title: 'text-base font-semibold text-foreground',
    body: 'p-5',
};
