<script setup lang="ts">
defineProps<{
    label: string;
    /** id of the control inside the slot, so clicking the label focuses it. */
    for?: string;
    required?: boolean;
    hint?: string;
    error?: string;
}>();
</script>

<template>
    <div class="space-y-1.5">
        <label
            :for="$props.for"
            class="text-foreground block text-sm font-medium"
        >
            {{ label }}
            <span v-if="required" class="text-destructive" aria-hidden="true"
                >*</span
            >
        </label>
        <slot />
        <p v-if="error" class="text-destructive text-sm">{{ error }}</p>
        <p v-else-if="hint" class="text-muted-foreground text-xs">
            {{ hint }}
        </p>
    </div>
</template>
