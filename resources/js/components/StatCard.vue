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
    <div
        class="border-border bg-card flex flex-col rounded-xl border p-5 shadow-xs"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-muted-foreground text-sm font-medium">
                    {{ label }}
                </p>
                <p
                    class="text-foreground mt-2 truncate text-2xl font-semibold tracking-tight tabular-nums md:text-3xl"
                >
                    {{ value
                    }}<span
                        v-if="unit"
                        class="text-muted-foreground ml-1 text-base font-medium"
                        >{{ unit }}</span
                    >
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
        <div
            v-if="hint || $slots.default"
            class="text-muted-foreground mt-auto pt-3 text-xs"
        >
            <slot>{{ hint }}</slot>
        </div>
    </div>
</template>
