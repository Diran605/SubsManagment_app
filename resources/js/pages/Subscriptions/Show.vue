<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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

const isStudentMoMo = computed(() => {
    return props.subscription.package?.payer_type === 'student_momo';
});

const totalCollected = computed(() => {
    return (props.subscription.collections || []).reduce((acc: number, c: any) => acc + parseFloat(c.amount || 0), 0);
});

const targetAmount = computed(() => parseFloat(props.subscription.amount || 0));
const remainingAmount = computed(() => Math.max(0, targetAmount.value - totalCollected.value));
const collectionPercent = computed(() => {
    return targetAmount.value > 0 ? Math.min(100, Math.round((totalCollected.value / targetAmount.value) * 100)) : 0;
});

const showCollectionModal = ref(false);
const collectionForm = useForm({
    amount: '',
    collection_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const submitCollection = () => {
    collectionForm.post(route('subscriptions.collections.store', props.subscription.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCollectionModal.value = false;
            collectionForm.reset();
            collectionForm.collection_date = new Date().toISOString().split('T')[0];
        },
    });
};

const deleteCollection = (id: number) => {
    if (confirm('Are you sure you want to delete this collection entry?')) {
        router.delete(route('subscriptions.collections.destroy', id), {
            preserveScroll: true,
        });
    }
};

const formatMoney = (val: number) =>
    val ? Number(val).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' FCFA' : '0 FCFA';
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
                            <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold mb-1">
                                {{ isStudentMoMo ? 'Annual Target Amount' : 'Amount' }}
                            </dt>
                            <dd class="font-medium text-foreground">{{ Number(props.subscription.amount).toLocaleString('en-US') }} FCFA</dd>
                        </div>
                        <div v-if="props.subscription.student_count">
                            <dt class="text-muted-foreground text-xs uppercase tracking-wider font-semibold mb-1">Enrolled Students</dt>
                            <dd class="font-medium text-foreground">{{ Number(props.subscription.student_count).toLocaleString() }}</dd>
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

                <!-- 📱 MOBILE MONEY COLLECTION HUB (FOR UNIVERSITIES / STUDENT-MOMO) -->
                <div v-if="isStudentMoMo" class="rounded-2xl border border-orange-200 dark:border-orange-900 bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-orange-200 dark:border-orange-900 p-5 bg-orange-50/60 dark:bg-orange-950/20 flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <h3 class="font-semibold text-foreground flex items-center gap-2">
                                <span>📱 Mobile Money Collections</span>
                                <span class="rounded bg-orange-500/10 px-2 py-0.5 text-xs text-orange-600 dark:text-orange-400 border border-orange-500/20">
                                    Student Funded
                                </span>
                            </h3>
                            <p class="text-xs text-muted-foreground mt-0.5">Track incoming student MoMo payments against the annual target</p>
                        </div>
                        <button
                            v-if="canEdit"
                            @click="showCollectionModal = true"
                            :class="buttonVariants({ variant: 'default' })"
                            class="bg-orange-500 hover:bg-orange-600 text-white"
                        >
                            ➕ Log MoMo Collection
                        </button>
                    </div>

                    <!-- Progress & Metrics -->
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="rounded-xl border border-border bg-muted/20 p-3">
                                <p class="text-xs text-muted-foreground font-medium uppercase">Target</p>
                                <p class="text-base sm:text-lg font-bold text-foreground mt-1">{{ formatMoney(targetAmount) }}</p>
                            </div>
                            <div class="rounded-xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50/50 dark:bg-emerald-950/20 p-3">
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium uppercase">Collected</p>
                                <p class="text-base sm:text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ formatMoney(totalCollected) }}</p>
                            </div>
                            <div class="rounded-xl border border-border bg-muted/20 p-3">
                                <p class="text-xs text-muted-foreground font-medium uppercase">Remaining</p>
                                <p class="text-base sm:text-lg font-bold text-muted-foreground mt-1">{{ formatMoney(remainingAmount) }}</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div>
                            <div class="flex justify-between text-xs font-medium text-foreground mb-1.5">
                                <span>Collection Progress</span>
                                <span :class="collectionPercent >= 100 ? 'text-green-500' : 'text-orange-500'">{{ collectionPercent }}%</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2.5 overflow-hidden">
                                <div
                                    class="h-2.5 rounded-full transition-all duration-500"
                                    :class="collectionPercent >= 100 ? 'bg-green-500' : 'bg-orange-500'"
                                    :style="{ width: collectionPercent + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Collections Table -->
                    <div class="border-t border-border">
                        <div class="px-5 py-3 bg-muted/30 border-b border-border">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Collection History</h4>
                        </div>
                        <div v-if="props.subscription.collections && props.subscription.collections.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-border bg-muted/40 text-xs text-muted-foreground">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium">Date</th>
                                        <th class="px-4 py-2 text-right font-medium">Amount</th>
                                        <th class="px-4 py-2 text-left font-medium">Notes</th>
                                        <th class="px-4 py-2 text-left font-medium">Recorded By</th>
                                        <th v-if="canEdit" class="px-4 py-2 text-right font-medium">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="col in props.subscription.collections" :key="col.id" class="hover:bg-muted/30">
                                        <td class="px-4 py-2.5 text-foreground font-medium">{{ new Date(col.collection_date).toLocaleDateString() }}</td>
                                        <td class="px-4 py-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ formatMoney(col.amount) }}</td>
                                        <td class="px-4 py-2.5 text-muted-foreground text-xs">{{ col.notes || '-' }}</td>
                                        <td class="px-4 py-2.5 text-muted-foreground text-xs">{{ col.recorder?.name || 'Staff' }}</td>
                                        <td v-if="canEdit" class="px-4 py-2.5 text-right">
                                            <button
                                                @click="deleteCollection(col.id)"
                                                class="text-xs text-destructive hover:underline"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="py-8 text-center text-xs text-muted-foreground italic">
                            No Mobile Money payments logged yet. Click "Log MoMo Collection" above to record one.
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
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-foreground text-sm">{{ props.subscription.package.name }}</span>
                                    <span v-if="props.subscription.package.pricing_model === 'per_student'" class="text-xs text-muted-foreground">
                                        {{ Number(props.subscription.package.price).toLocaleString('en-US') }} FCFA / student
                                    </span>
                                </div>
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

        <!-- Log MoMo Collection Modal -->
        <div
            v-if="showCollectionModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-2xl border border-border bg-card p-6 shadow-xl">
                <div class="flex items-center justify-between pb-3 border-b border-border mb-4">
                    <h3 class="text-lg font-semibold text-foreground">Log Mobile Money Collection</h3>
                    <button @click="showCollectionModal = false" class="text-muted-foreground hover:text-foreground text-sm font-bold">✕</button>
                </div>

                <form @submit.prevent="submitCollection" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">
                            Amount Received (FCFA) <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model.number="collectionForm.amount"
                            type="number"
                            min="1"
                            step="0.01"
                            required
                            placeholder="e.g. 500000"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 text-sm focus:border-primary focus:ring-primary"
                        />
                        <p v-if="collectionForm.errors.amount" class="text-xs text-destructive mt-1">{{ collectionForm.errors.amount }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">
                            Collection Date <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model="collectionForm.collection_date"
                            type="date"
                            required
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 text-sm focus:border-primary focus:ring-primary"
                        />
                        <p v-if="collectionForm.errors.collection_date" class="text-xs text-destructive mt-1">{{ collectionForm.errors.collection_date }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">Notes / Batch Description</label>
                        <textarea
                            v-model="collectionForm.notes"
                            rows="2"
                            placeholder="e.g. Week 2 student registration batch"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 text-sm focus:border-primary focus:ring-primary"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-border">
                        <button
                            type="button"
                            @click="showCollectionModal = false"
                            class="px-4 py-2 text-xs font-medium rounded-md border border-border hover:bg-muted transition-colors text-foreground"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="collectionForm.processing"
                            :class="buttonVariants({ variant: 'default' })"
                            class="bg-orange-500 hover:bg-orange-600 text-white"
                        >
                            {{ collectionForm.processing ? 'Saving...' : 'Record Payment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
