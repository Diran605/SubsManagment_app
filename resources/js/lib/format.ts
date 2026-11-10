/** Shared display formatting so every page shows money and dates the same way. */

const moneyFormat = new Intl.NumberFormat('en-US', {
    maximumFractionDigits: 2,
});

/** 1250000 -> "1,250,000" (add the "FCFA" unit next to it in the template). */
export function formatMoney(value: number | string | null | undefined): string {
    return moneyFormat.format(Number(value) || 0);
}

const dateFormat = new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    timeZone: 'UTC',
});

/** "2026-03-01" or an ISO timestamp -> "01 Mar 2026". Empty values show "—". */
export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    // Plain dates are read as UTC midnight so they never shift by a day.
    const date = new Date(
        /^\d{4}-\d{2}-\d{2}$/.test(value) ? `${value}T00:00:00Z` : value,
    );

    return Number.isNaN(date.getTime()) ? '—' : dateFormat.format(date);
}

/** Placeholder for empty optional fields. */
export function orDash(value: string | null | undefined): string {
    return value && String(value).trim() !== '' ? value : '—';
}
