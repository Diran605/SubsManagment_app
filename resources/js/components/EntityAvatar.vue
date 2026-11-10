<script setup lang="ts">
import { computed } from 'vue';
import { getInitials } from '@/composables/useInitials';

const props = defineProps<{ name: string }>();

// Soft tints from the brand palette; the same name always gets the same colour.
const tints = [
    'bg-primary/10 text-primary',
    'bg-orange-500/10 text-orange-600 dark:text-orange-400',
    'bg-info/10 text-info',
    'bg-success/10 text-success',
    'bg-warning/10 text-warning',
];

const tint = computed(() => {
    let hash = 0;
    for (const char of props.name ?? '') {
        hash = (hash * 31 + char.charCodeAt(0)) >>> 0;
    }

    return tints[hash % tints.length];
});
</script>

<template>
    <span
        class="flex size-9 shrink-0 items-center justify-center rounded-lg text-xs font-semibold"
        :class="tint"
        aria-hidden="true"
    >
        {{ getInitials(name) || '?' }}
    </span>
</template>
