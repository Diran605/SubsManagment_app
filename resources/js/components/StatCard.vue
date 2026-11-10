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
    <!-- @container: the value's font size follows the card's own width, so a
         long amount like 5,154,000 FCFA always fits instead of being cut off. -->
    <div
        class="border-border bg-card @container flex flex-col rounded-xl border p-5 shadow-xs"
    >
        <div class="flex items-center gap-2.5">
            <div
                v-if="$slots.icon"
                class="flex size-8 shrink-0 items-center justify-center rounded-lg [&>svg]:size-4"
                :class="toneClasses[tone]"
                aria-hidden="true"
            >
                <slot name="icon" />
            </div>
            <p class="text-muted-foreground text-sm leading-tight font-medium">
                {{ label }}
            </p>
        </div>

        <p
            class="text-foreground mt-3 flex flex-wrap items-baseline gap-x-1.5 font-semibold tracking-tight tabular-nums"
        >
            <span
                class="text-xl leading-tight whitespace-nowrap @[13rem]:text-2xl @[17rem]:text-3xl"
                >{{ value }}</span
            >
            <span
                v-if="unit"
                class="text-muted-foreground text-xs font-medium tracking-normal @[13rem]:text-sm"
                >{{ unit }}</span
            >
        </p>

        <div
            v-if="hint || $slots.default"
            class="text-muted-foreground mt-auto pt-3 text-xs"
        >
            <slot>{{ hint }}</slot>
        </div>
    </div>
</template>
