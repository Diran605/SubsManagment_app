<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canEdit = computed(() => page.props.auth?.user?.permissions?.includes('edit subscriptions'));
const canRestore = computed(() => page.props.auth?.user?.permissions?.includes('restore subscriptions'));

const props = defineProps<{
    subscription: any;
}>();

const statusColor = computed(() => {
    switch (props.subscription.status_badge) {
        case 'active': return 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50';
        case 'expiring_soon': return 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/50';
        case 'expired': return 'bg-destructive/10 text-destructive border-destructive/20';
        case 'renewed': return 'bg-muted text-muted-foreground border-border';
        default: return 'bg-muted text-muted-foreground border-border';
    }
});

const formattedStatus = computed(() => {
    return props.subscription.status_badge ? props.subscription.status_badge.replace('_', ' ').toUpperCase() : 'UNKNOWN';
});

</script>

<template>
    <Head title="Subscription Details" />

    <div class="p-4 md:p-8 mx-auto max-w-4xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Subscription Details</h1>
            </div>
            <Link :href="route('subscriptions.index')" class="text-sm font-medium text-muted-foreground hover:text-foreground">
                &larr; Back to Subscriptions
            </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Left Column: Core Info -->
            <div class="md:col-span-2 space-y-6">
                <!-- Summary Card -->
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                    <div class="flex justify-between items-start mb-6 border-b border-border pb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-foreground">{{ props.subscription.service?.name }}</h2>
                            <p class="text-sm text-muted-foreground mt-1">for <Link :href="route('businesses.show', props.subscription.business_id)" class="font-medium text-primary hover:underline">{{ props.subscription.business?.name }}</Link></p>
                        </div>
                        <span :class="['px-3 py-1 rounded-full text-xs font-bold border', statusColor]">
                            {{ formattedStatus }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                        <div>
                            <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold mb-1">Mode</dt>
                            <dd class="font-medium text-foreground capitalize">{{ props.subscription.mode }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold mb-1">Amount</dt>
                            <dd class="font-medium text-foreground">{{ Number(props.subscription.amount).toLocaleString('en-US') }} FCFA</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold mb-1">Start Date</dt>
                            <dd class="font-medium text-foreground">{{ new Date(props.subscription.start_date).toLocaleDateString() }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold mb-1">End Date</dt>
                            <dd class="font-medium text-foreground">{{ new Date(props.subscription.end_date).toLocaleDateString() }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Package & Modules Configuration -->
                <div class="rounded-2xl border border-border bg-card shadow-sm flex flex-col">
                    <div class="border-b border-border p-4 bg-muted/20">
                        <h3 class="font-semibold text-foreground">Configuration</h3>
                    </div>
                    <div class="p-4">
                        <div class="mb-4">
                            <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Package</h4>
                            <div v-if="props.subscription.package" class="bg-muted/30 rounded-lg p-3 border border-border inline-block w-full">
                                <span class="font-medium text-foreground text-sm">{{ props.subscription.package.name }}</span>
                                <p class="text-xs text-muted-foreground mt-1">{{ props.subscription.package.description || 'No description' }}</p>
                            </div>
                            <p v-else class="text-sm text-muted-foreground italic">Custom Package / Not specified</p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Included Modules</h4>
                            <div v-if="props.subscription.modules && props.subscription.modules.length > 0" class="flex flex-wrap gap-2">
                                <span v-for="mod in props.subscription.modules" :key="mod.id" class="inline-flex items-center rounded-md bg-primary/10 px-2 py-1 text-xs font-medium text-primary ring-1 ring-inset ring-primary/20">
                                    {{ mod.name }}
                                </span>
                            </div>
                            <p v-else class="text-sm text-muted-foreground italic">No modules selected</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="props.subscription.notes" class="rounded-2xl border border-border bg-card shadow-sm flex flex-col">
                    <div class="border-b border-border p-4 bg-muted/20">
                        <h3 class="font-semibold text-foreground">Notes</h3>
                    </div>
                    <div class="p-4 text-sm text-foreground whitespace-pre-wrap">
                        {{ props.subscription.notes }}
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions & Metadata -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="rounded-2xl border border-border bg-card p-4 shadow-sm flex flex-col gap-3">
                    <template v-if="props.subscription.status_badge !== 'trashed' && canEdit">
                        <Link
                            v-if="props.subscription.status_badge !== 'active' && props.subscription.status_badge !== 'renewed'"
                            :href="route('subscriptions.renewForm', props.subscription.id)"
                            :class="buttonVariants({ variant: 'default' })"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white w-full"
                        >
                            Renew Subscription
                        </Link>
                        <Link
                            :href="route('subscriptions.edit', props.subscription.id)"
                            :class="buttonVariants({ variant: 'secondary' })"
                            class="w-full"
                        >
                            Edit Subscription
                        </Link>
                    </template>
                    <button
                        v-else-if="canRestore"
                        @click="confirm('Are you sure you want to restore this subscription?') && router.post(route('subscriptions.restore', props.subscription.id))"
                        :class="buttonVariants({ variant: 'secondary' })"
                        class="w-full text-amber-600 dark:text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-900/30 border-amber-200 dark:border-amber-800/50"
                    >
                        Restore Subscription
                    </button>
                </div>

                <!-- Metadata -->
                <div class="rounded-2xl border border-border bg-card p-4 shadow-sm text-sm">
                    <h3 class="font-semibold text-foreground mb-3 border-b border-border pb-2">Record Info</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Added By</span>
                            <span class="font-medium text-foreground">{{ props.subscription.creator?.name || 'System' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Created At</span>
                            <span class="font-medium text-foreground">{{ new Date(props.subscription.created_at).toLocaleDateString() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Updated At</span>
                            <span class="font-medium text-foreground">{{ new Date(props.subscription.updated_at).toLocaleDateString() }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
