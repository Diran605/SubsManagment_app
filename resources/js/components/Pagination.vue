<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: { url: string | null; label: string; active: boolean }[];
    from?: number | null;
    to?: number | null;
    total?: number | null;
}>();
</script>

<template>
    <div
        v-if="links && links.length > 3"
        class="flex flex-col items-center justify-between gap-3 border-t border-border px-4 py-3 sm:flex-row"
    >
        <p v-if="total" class="text-sm text-muted-foreground">
            Showing <span class="font-medium text-foreground">{{ from }}</span>–<span class="font-medium text-foreground">{{ to }}</span>
            of <span class="font-medium text-foreground">{{ total }}</span>
        </p>
        <span v-else />
        <nav class="flex flex-wrap items-center gap-1" aria-label="Pagination">
            <Link
                v-for="(link, key) in links"
                :key="key"
                :href="link.url || ''"
                v-html="link.label"
                preserve-scroll
                :aria-current="link.active ? 'page' : undefined"
                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2.5 text-sm font-medium transition-colors"
                :class="[
                    link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
            />
        </nav>
    </div>
</template>
