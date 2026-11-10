import { reactive } from 'vue';

export type ConfirmOptions = {
    title: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    /** "danger" for deletes, "default" for safe actions such as restore. */
    tone?: 'danger' | 'default';
};

type ConfirmState = Required<Omit<ConfirmOptions, 'description'>> & {
    open: boolean;
    description: string;
};

// One shared dialog for the whole app, rendered by <ConfirmDialog /> in the layout.
export const confirmState = reactive<ConfirmState>({
    open: false,
    title: '',
    description: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    tone: 'default',
});

let resolver: ((value: boolean) => void) | null = null;

export function settleConfirm(value: boolean): void {
    confirmState.open = false;
    resolver?.(value);
    resolver = null;
}

/**
 * In-app replacement for window.confirm(). Resolves true when the user
 * confirms, false when they cancel, press Escape or click outside.
 */
export function useConfirm() {
    return (options: ConfirmOptions): Promise<boolean> => {
        // A second request while one is open cancels the first.
        resolver?.(false);

        Object.assign(confirmState, {
            description: '',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            tone: 'default',
            ...options,
            open: true,
        });

        return new Promise((resolve) => {
            resolver = resolve;
        });
    };
}
