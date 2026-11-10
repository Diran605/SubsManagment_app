<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
    >
        <div class="min-w-0">
            <slot name="eyebrow" />
            <h1
                class="text-foreground truncate text-2xl font-semibold tracking-tight md:text-3xl"
            >
                {{ title }}
            </h1>
            <p
                v-if="description || $slots.description"
                class="text-muted-foreground mt-1 text-sm"
            >
                <slot name="description">{{ description }}</slot>
            </p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
