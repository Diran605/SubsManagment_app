<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        unit?: string;
        hint?: string;
        tone?: 'primary' | 'accent' | 'success' | 'info' | 'warning';
    }>(),
    { tone: 'primary' },
);

const toneClasses: Record<string, string> = {
    primary: 'bg-primary/10 text-primary',
    accent: 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
    success: 'bg-success/10 text-success',
    info: 'bg-info/10 text-info',
    warning: 'bg-warning/10 text-warning',
};
</script>

<template>
    <div class="flex flex-col rounded-xl border border-border bg-card p-5 shadow-xs">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-medium text-muted-foreground">{{ label }}</p>
                <p class="mt-2 truncate text-2xl font-semibold tracking-tight text-foreground tabular-nums md:text-3xl">
                    {{ value }}<span v-if="unit" class="ml-1 text-base font-medium text-muted-foreground">{{ unit }}</span>
                </p>
            </div>
            <div
                v-if="$slots.icon"
                class="flex size-10 shrink-0 items-center justify-center rounded-lg [&>svg]:size-5"
                :class="toneClasses[tone]"
                aria-hidden="true"
            >
                <slot name="icon" />
            </div>
        </div>
        <div v-if="hint || $slots.default" class="mt-auto pt-3 text-xs text-muted-foreground">
            <slot>{{ hint }}</slot>
        </div>
    </div>
</template>
