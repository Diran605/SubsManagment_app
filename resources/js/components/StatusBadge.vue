<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{ status: string }>();

const variants: Record<
    string,
    'success' | 'warning' | 'danger' | 'muted' | 'info'
> = {
    active: 'success',
    expiring_soon: 'warning',
    expired: 'danger',
    renewed: 'info',
    trashed: 'muted',
};

const labels: Record<string, string> = {
    active: 'Active',
    expiring_soon: 'Expiring soon',
    expired: 'Expired',
    renewed: 'Renewed',
    trashed: 'Deleted',
};

const variant = computed(() => variants[props.status] ?? 'muted');
const label = computed(
    () => labels[props.status] ?? props.status.replace(/_/g, ' '),
);
</script>

<template>
    <Badge :variant="variant">
        <span class="size-1.5 rounded-full bg-current" aria-hidden="true" />
        {{ label }}
    </Badge>
</template>
