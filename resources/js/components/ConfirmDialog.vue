<script setup lang="ts">
import { AlertTriangle, RotateCcw } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { confirmState, settleConfirm } from '@/composables/useConfirm';

const onOpenChange = (open: boolean) => {
    if (!open) {
        settleConfirm(false);
    }
};
</script>

<template>
    <Dialog :open="confirmState.open" @update:open="onOpenChange">
        <DialogContent class="sm:max-w-md" :show-close-button="false">
            <DialogHeader class="flex-row items-start gap-4 text-left">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-full"
                    :class="
                        confirmState.tone === 'danger'
                            ? 'bg-destructive/10 text-destructive'
                            : 'bg-primary/10 text-primary'
                    "
                >
                    <AlertTriangle
                        v-if="confirmState.tone === 'danger'"
                        class="size-5"
                    />
                    <RotateCcw v-else class="size-5" />
                </div>
                <div class="space-y-1.5">
                    <DialogTitle>{{ confirmState.title }}</DialogTitle>
                    <DialogDescription v-if="confirmState.description">
                        {{ confirmState.description }}
                    </DialogDescription>
                </div>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="outline" @click="settleConfirm(false)">
                    {{ confirmState.cancelText }}
                </Button>
                <Button
                    :variant="
                        confirmState.tone === 'danger'
                            ? 'destructive'
                            : 'default'
                    "
                    @click="settleConfirm(true)"
                >
                    {{ confirmState.confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
