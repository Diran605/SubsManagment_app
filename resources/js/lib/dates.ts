/**
 * Date helpers for plain "YYYY-MM-DD" strings.
 *
 * Everything is done in UTC so results don't shift by a day depending on the
 * browser's timezone.
 */

const pad = (n: number) => String(n).padStart(2, '0');

const toDate = (value: string): Date | null => {
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(value ?? '');

    if (!match) {
        return null;
    }

    return new Date(Date.UTC(Number(match[1]), Number(match[2]) - 1, Number(match[3])));
};

const toIso = (date: Date) =>
    `${date.getUTCFullYear()}-${pad(date.getUTCMonth() + 1)}-${pad(date.getUTCDate())}`;

const daysInMonth = (year: number, monthIndex: number) =>
    new Date(Date.UTC(year, monthIndex + 1, 0)).getUTCDate();

/** Today's date as "YYYY-MM-DD" in the user's local calendar. */
export function today(): string {
    const now = new Date();

    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}

/** Add days to a date string. */
export function addDays(value: string, days: number): string {
    const date = toDate(value);

    if (!date) {
        return '';
    }

    date.setUTCDate(date.getUTCDate() + days);

    return toIso(date);
}

/**
 * Add calendar months, clamping to the end of the month so that
 * Jan 31 + 1 month is Feb 28/29 rather than early March.
 */
export function addMonths(value: string, months: number): string {
    const date = toDate(value);

    if (!date) {
        return '';
    }

    const target = date.getUTCMonth() + months;
    const year = date.getUTCFullYear() + Math.floor(target / 12);
    const month = ((target % 12) + 12) % 12;
    const day = Math.min(date.getUTCDate(), daysInMonth(year, month));

    return toIso(new Date(Date.UTC(year, month, day)));
}

/**
 * Last day covered by a subscription that starts on `start` and runs for
 * `months` months. A 12-month subscription starting 2026-01-01 ends 2026-12-31,
 * so a renewal can start the next day without overlapping.
 */
export function subscriptionEndDate(start: string, months: number): string {
    return addDays(addMonths(start, months), -1);
}

/**
 * Whole months covered by a subscription from `start` to `end` (inclusive).
 * Returns at least 1.
 */
export function monthsCovered(start: string, end: string): number {
    const from = toDate(start);
    const until = toDate(addDays(end, 1));

    if (!from || !until) {
        return 1;
    }

    let months =
        (until.getUTCFullYear() - from.getUTCFullYear()) * 12 +
        (until.getUTCMonth() - from.getUTCMonth());

    if (until.getUTCDate() < from.getUTCDate()) {
        months -= 1;
    }

    return Math.max(1, months);
}
