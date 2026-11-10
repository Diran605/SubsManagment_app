/**
 * Wrap a long axis label onto several lines of roughly `maxLength` characters
 * so category names stay readable under bar charts.
 */
export function wrapLabel(label: unknown, maxLength = 12): string | string[] {
    if (typeof label !== 'string') {
        return String(label);
    }

    const lines: string[] = [];
    let currentLine = '';

    label.split(' ').forEach((word) => {
        if (
            (currentLine + word).length > maxLength &&
            currentLine.trim().length > 0
        ) {
            lines.push(currentLine.trim());
            currentLine = '';
        }
        currentLine += word + ' ';
    });

    if (currentLine.trim().length > 0) {
        lines.push(currentLine.trim());
    }

    return lines;
}

/** Tick callback for Chart.js category axes that wraps long labels. */
export function wrappedTickCallback(
    this: { getLabelForValue: (value: number) => unknown },
    value: string | number,
): string | string[] {
    return wrapLabel(this.getLabelForValue(Number(value)));
}
