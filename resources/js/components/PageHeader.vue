<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';

defineProps<{
    title: string;
    description?: string;
    /** Optional "back" link shown above the title on detail and form pages. */
    backHref?: string;
    backLabel?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
    >
        <div class="min-w-0">
            <Link
                v-if="backHref"
                :href="backHref"
                class="text-muted-foreground hover:text-foreground mb-2 inline-flex items-center gap-1.5 text-sm font-medium transition-colors"
            >
                <ArrowLeft class="size-4" />
                {{ backLabel ?? 'Back' }}
            </Link>
            <slot name="eyebrow" />
            <h1
                class="text-foreground truncate text-2xl font-semibold tracking-tight md:text-3xl"
            >
                <slot name="title">{{ title }}</slot>
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
