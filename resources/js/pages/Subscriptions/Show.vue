<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import subscriptionsRoutes from '@/routes/subscriptions';
import businessesRoutes from '@/routes/businesses';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { buttonVariants } from '@/components/ui/button';
import StatusBadge from '@/components/StatusBadge.vue';

const page = usePage<any>();
const canCreate = computed(() =>
    page.props.auth?.user?.permissions?.includes('create subscriptions'),
);
const canEdit = computed(() =>
    page.props.auth?.user?.permissions?.includes('edit subscriptions'),
);
const canRestore = computed(() =>
    page.props.auth?.user?.permissions?.includes('restore subscriptions'),
);

const props = defineProps<{
    subscription: any;
}>();

const isStudentMoMo = computed(() => {
    return props.subscription.package?.payer_type === 'student_momo';
});

const totalCollected = computed(() => {
    return (props.subscription.collections || []).reduce(
        (acc: number, c: any) => acc + parseFloat(c.amount || 0),
        0,
    );
});

const targetAmount = computed(() => parseFloat(props.subscription.amount || 0));
const remainingAmount = computed(() =>
    Math.max(0, targetAmount.value - totalCollected.value),
);
const collectionPercent = computed(() => {
    return targetAmount.value > 0
        ? Math.min(
              100,
              Math.round((totalCollected.value / targetAmount.value) * 100),
          )
        : 0;
});

const showCollectionModal = ref(false);
const collectionForm = useForm({
    amount: '',
    collection_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const submitCollection = () => {
    collectionForm.post(
        subscriptionsRoutes.collections.store.url(props.subscription.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showCollectionModal.value = false;
                collectionForm.reset();
                collectionForm.collection_date = new Date()
                    .toISOString()
                    .split('T')[0];
            },
        },
    );
};

const confirmAction = useConfirm();
const deleteCollection = async (id: number) => {
    if (
        await confirmAction({
            title: 'Delete this payment?',
            description:
                "The amount will no longer count towards money collected. This can't be undone.",
            confirmText: 'Delete payment',
            tone: 'danger',
        })
    ) {
        router.delete(subscriptionsRoutes.collections.destroy.url(id), {
            preserveScroll: true,
        });
    }
};

const restoreSubscription = async () => {
    if (
        await confirmAction({
            title: 'Restore this subscription?',
            description:
                'It goes back into the subscriptions list and counts towards revenue again.',
            confirmText: 'Restore',
            tone: 'default',
        })
    ) {
        router.post(subscriptionsRoutes.restore.url(props.subscription.id));
    }
};

const formatMoney = (val: number) =>
    val
        ? Number(val).toLocaleString('en-US', {
              minimumFractionDigits: 0,
              maximumFractionDigits: 0,
          }) + ' FCFA'
        : '0 FCFA';
</script>

<template>
    <Head title="Subscription Details" />

    <div class="mx-auto max-w-4xl p-4 md:p-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1
                    class="text-foreground text-2xl font-semibold tracking-tight md:text-3xl"
                >
                    Subscription Details
                </h1>
            </div>
            <Link
                :href="subscriptionsRoutes.index.url()"
                class="text-muted-foreground hover:text-foreground text-sm font-medium"
            >
                &larr; Back to Subscriptions
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Left Column: Core Info -->
            <div class="space-y-6 md:col-span-2">
                <!-- Summary Card -->
                <div
                    class="border-border bg-card rounded-xl border p-6 shadow-sm"
                >
                    <div
                        class="border-border mb-6 flex items-start justify-between border-b pb-4"
                    >
                        <div>
                            <h2 class="text-foreground text-xl font-semibold">
                                {{ props.subscription.service?.name }}
                            </h2>
                            <p class="text-muted-foreground mt-1 text-sm">
                                for
                                <Link
                                    :href="
                                        businessesRoutes.show.url(
                                            props.subscription.business_id,
                                        )
                                    "
                                    class="text-primary font-medium hover:underline"
                                    >{{
                                        props.subscription.business?.name
                                    }}</Link
                                >
                            </p>
                        </div>
                        <StatusBadge
                            :status="props.subscription.status_badge"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt
                                class="text-muted-foreground mb-1 text-xs font-semibold tracking-wider uppercase"
                            >
                                Mode
                            </dt>
                            <dd class="text-foreground font-medium capitalize">
                                {{ props.subscription.mode }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-muted-foreground mb-1 text-xs font-semibold tracking-wider uppercase"
                            >
                                {{
                                    isStudentMoMo
                                        ? 'Annual Target Amount'
                                        : 'Amount'
                                }}
                            </dt>
                            <dd class="text-foreground font-medium">
                                {{
                                    Number(
                                        props.subscription.amount,
                                    ).toLocaleString('en-US')
                                }}
                                FCFA
                            </dd>
                        </div>
                        <div v-if="props.subscription.student_count">
                            <dt
                                class="text-muted-foreground mb-1 text-xs font-semibold tracking-wider uppercase"
                            >
                                Enrolled Students
                            </dt>
                            <dd class="text-foreground font-medium">
                                {{
                                    Number(
                                        props.subscription.student_count,
                                    ).toLocaleString()
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-muted-foreground mb-1 text-xs font-semibold tracking-wider uppercase"
                            >
                                Start Date
                            </dt>
                            <dd class="text-foreground font-medium">
                                {{
                                    new Date(
                                        props.subscription.start_date,
                                    ).toLocaleDateString()
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-muted-foreground mb-1 text-xs font-semibold tracking-wider uppercase"
                            >
                                End Date
                            </dt>
                            <dd class="text-foreground font-medium">
                                {{
                                    new Date(
                                        props.subscription.end_date,
                                    ).toLocaleDateString()
                                }}
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- 📱 MOBILE MONEY COLLECTION HUB (FOR UNIVERSITIES / STUDENT-MOMO) -->
                <div
                    v-if="isStudentMoMo"
                    class="bg-card overflow-hidden rounded-xl border border-orange-200 shadow-sm dark:border-orange-900"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-orange-200 bg-orange-50/60 p-5 dark:border-orange-900 dark:bg-orange-950/20"
                    >
                        <div>
                            <h3
                                class="text-foreground flex items-center gap-2 font-semibold"
                            >
                                <span>📱 Mobile Money Collections</span>
                                <span
                                    class="rounded border border-orange-500/20 bg-orange-500/10 px-2 py-0.5 text-xs text-orange-600 dark:text-orange-400"
                                >
                                    Student Funded
                                </span>
                            </h3>
                            <p class="text-muted-foreground mt-0.5 text-xs">
                                Track incoming student MoMo payments against the
                                annual target
                            </p>
                        </div>
                        <button
                            v-if="canEdit"
                            @click="showCollectionModal = true"
                            :class="buttonVariants({ variant: 'default' })"
                            class="bg-orange-500 text-white hover:bg-orange-600"
                        >
                            ➕ Log MoMo Collection
                        </button>
                    </div>

                    <!-- Progress & Metrics -->
                    <div class="space-y-4 p-6">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div
                                class="border-border bg-muted/20 rounded-xl border p-3"
                            >
                                <p
                                    class="text-muted-foreground text-xs font-medium uppercase"
                                >
                                    Target
                                </p>
                                <p
                                    class="text-foreground mt-1 text-base font-bold sm:text-lg"
                                >
                                    {{ formatMoney(targetAmount) }}
                                </p>
                            </div>
                            <div
                                class="border-success/30 bg-success/10 rounded-xl border p-3"
                            >
                                <p
                                    class="text-success text-xs font-medium uppercase"
                                >
                                    Collected
                                </p>
                                <p
                                    class="text-success mt-1 text-base font-bold sm:text-lg"
                                >
                                    {{ formatMoney(totalCollected) }}
                                </p>
                            </div>
                            <div
                                class="border-border bg-muted/20 rounded-xl border p-3"
                            >
                                <p
                                    class="text-muted-foreground text-xs font-medium uppercase"
                                >
                                    Remaining
                                </p>
                                <p
                                    class="text-muted-foreground mt-1 text-base font-bold sm:text-lg"
                                >
                                    {{ formatMoney(remainingAmount) }}
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div>
                            <div
                                class="text-foreground mb-1.5 flex justify-between text-xs font-medium"
                            >
                                <span>Collection Progress</span>
                                <span
                                    :class="
                                        collectionPercent >= 100
                                            ? 'text-success'
                                            : 'text-orange-500'
                                    "
                                    >{{ collectionPercent }}%</span
                                >
                            </div>
                            <div
                                class="bg-muted h-2.5 w-full overflow-hidden rounded-full"
                            >
                                <div
                                    class="h-2.5 rounded-full transition-all duration-500"
                                    :class="
                                        collectionPercent >= 100
                                            ? 'bg-success'
                                            : 'bg-orange-500'
                                    "
                                    :style="{ width: collectionPercent + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Collections Table -->
                    <div class="border-border border-t">
                        <div
                            class="bg-muted/30 border-border border-b px-5 py-3"
                        >
                            <h4
                                class="text-muted-foreground text-xs font-semibold tracking-wider uppercase"
                            >
                                Collection History
                            </h4>
                        </div>
                        <div
                            v-if="
                                props.subscription.collections &&
                                props.subscription.collections.length > 0
                            "
                            class="overflow-x-auto"
                        >
                            <table class="w-full text-sm">
                                <thead
                                    class="border-border bg-muted/40 text-muted-foreground border-b text-xs"
                                >
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left font-medium"
                                        >
                                            Date
                                        </th>
                                        <th
                                            class="px-4 py-2 text-right font-medium"
                                        >
                                            Amount
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left font-medium"
                                        >
                                            Notes
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left font-medium"
                                        >
                                            Recorded By
                                        </th>
                                        <th
                                            v-if="canEdit"
                                            class="px-4 py-2 text-right font-medium"
                                        >
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-border divide-y">
                                    <tr
                                        v-for="col in props.subscription
                                            .collections"
                                        :key="col.id"
                                        class="hover:bg-muted/30"
                                    >
                                        <td
                                            class="text-foreground px-4 py-2.5 font-medium"
                                        >
                                            {{
                                                new Date(
                                                    col.collection_date,
                                                ).toLocaleDateString()
                                            }}
                                        </td>
                                        <td
                                            class="text-success px-4 py-2.5 text-right font-semibold"
                                        >
                                            {{ formatMoney(col.amount) }}
                                        </td>
                                        <td
                                            class="text-muted-foreground px-4 py-2.5 text-xs"
                                        >
                                            {{ col.notes || '—' }}
                                        </td>
                                        <td
                                            class="text-muted-foreground px-4 py-2.5 text-xs"
                                        >
                                            {{ col.recorder?.name || 'Staff' }}
                                        </td>
                                        <td
                                            v-if="canEdit"
                                            class="px-4 py-2.5 text-right"
                                        >
                                            <button
                                                @click="
                                                    deleteCollection(col.id)
                                                "
                                                class="text-destructive text-xs hover:underline"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            v-else
                            class="text-muted-foreground py-8 text-center text-xs italic"
                        >
                            No Mobile Money payments logged yet. Click "Log MoMo
                            Collection" above to record one.
                        </div>
                    </div>
                </div>

                <!-- Package & Modules Configuration -->
                <div
                    class="border-border bg-card flex flex-col rounded-xl border shadow-xs"
                >
                    <div class="border-border bg-muted/20 border-b p-4">
                        <h3 class="text-foreground font-semibold">
                            Configuration
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="mb-4">
                            <h4
                                class="text-muted-foreground mb-2 text-xs font-semibold tracking-wider uppercase"
                            >
                                Package
                            </h4>
                            <div
                                v-if="props.subscription.package"
                                class="bg-muted/30 border-border inline-block w-full rounded-lg border p-3"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-foreground text-sm font-medium"
                                        >{{
                                            props.subscription.package.name
                                        }}</span
                                    >
                                    <span
                                        v-if="
                                            props.subscription.package
                                                .pricing_model === 'per_student'
                                        "
                                        class="text-muted-foreground text-xs"
                                    >
                                        {{
                                            Number(
                                                props.subscription.package
                                                    .price,
                                            ).toLocaleString('en-US')
                                        }}
                                        FCFA / student
                                    </span>
                                </div>
                                <p class="text-muted-foreground mt-1 text-xs">
                                    {{
                                        props.subscription.package
                                            .description || 'No description'
                                    }}
                                </p>
                            </div>
                            <p
                                v-else
                                class="text-muted-foreground text-sm italic"
                            >
                                Custom Package / Not specified
                            </p>
                        </div>

                        <div>
                            <h4
                                class="text-muted-foreground mb-2 text-xs font-semibold tracking-wider uppercase"
                            >
                                Included Modules
                            </h4>
                            <div
                                v-if="
                                    props.subscription.modules &&
                                    props.subscription.modules.length > 0
                                "
                                class="flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="mod in props.subscription.modules"
                                    :key="mod.id"
                                    class="bg-primary/10 text-primary ring-primary/20 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                >
                                    {{ mod.name }}
                                </span>
                            </div>
                            <p
                                v-else
                                class="text-muted-foreground text-sm italic"
                            >
                                No modules selected
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div
                    v-if="props.subscription.notes"
                    class="border-border bg-card flex flex-col rounded-xl border shadow-xs"
                >
                    <div class="border-border bg-muted/20 border-b p-4">
                        <h3 class="text-foreground font-semibold">Notes</h3>
                    </div>
                    <div
                        class="text-foreground p-4 text-sm whitespace-pre-wrap"
                    >
                        {{ props.subscription.notes }}
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions & Metadata -->
            <div class="space-y-6">
                <!-- Actions -->
                <div
                    class="border-border bg-card flex flex-col gap-3 rounded-xl border p-4 shadow-sm"
                >
                    <template
                        v-if="props.subscription.status_badge !== 'trashed'"
                    >
                        <Link
                            v-if="
                                canCreate &&
                                props.subscription.status_badge !== 'active' &&
                                props.subscription.status_badge !== 'renewed'
                            "
                            :href="
                                subscriptionsRoutes.createRenewal.url(
                                    props.subscription.id,
                                )
                            "
                            :class="buttonVariants({ variant: 'default' })"
                            class="bg-success text-success-foreground hover:bg-success/90 w-full"
                        >
                            Renew subscription
                        </Link>
                        <Link
                            v-if="canEdit"
                            :href="
                                subscriptionsRoutes.edit.url(
                                    props.subscription.id,
                                )
                            "
                            :class="buttonVariants({ variant: 'secondary' })"
                            class="w-full"
                        >
                            Edit subscription
                        </Link>
                    </template>
                    <button
                        v-else-if="canRestore"
                        @click="restoreSubscription"
                        :class="buttonVariants({ variant: 'secondary' })"
                        class="text-warning hover:bg-warning/10 border-warning/30 w-full"
                    >
                        Restore subscription
                    </button>
                </div>

                <!-- Metadata -->
                <div
                    class="border-border bg-card rounded-xl border p-4 text-sm shadow-sm"
                >
                    <h3
                        class="text-foreground border-border mb-3 border-b pb-2 font-semibold"
                    >
                        Record Info
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Added By</span>
                            <span class="text-foreground font-medium">{{
                                props.subscription.creator?.name || 'System'
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground"
                                >Created At</span
                            >
                            <span class="text-foreground font-medium">{{
                                new Date(
                                    props.subscription.created_at,
                                ).toLocaleDateString()
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground"
                                >Updated At</span
                            >
                            <span class="text-foreground font-medium">{{
                                new Date(
                                    props.subscription.updated_at,
                                ).toLocaleDateString()
                            }}</span>
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
            <div
                class="border-border bg-card w-full max-w-md rounded-xl border p-6 shadow-xl"
            >
                <div
                    class="border-border mb-4 flex items-center justify-between border-b pb-3"
                >
                    <h3 class="text-foreground text-lg font-semibold">
                        Log Mobile Money Collection
                    </h3>
                    <button
                        @click="showCollectionModal = false"
                        class="text-muted-foreground hover:text-foreground text-sm font-bold"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitCollection" class="space-y-4">
                    <div>
                        <label
                            class="text-foreground mb-1 block text-xs font-medium"
                        >
                            Amount Received (FCFA)
                            <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model.number="collectionForm.amount"
                            type="number"
                            min="1"
                            step="0.01"
                            required
                            placeholder="e.g. 500000"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                        />
                        <p
                            v-if="collectionForm.errors.amount"
                            class="text-destructive mt-1 text-xs"
                        >
                            {{ collectionForm.errors.amount }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="text-foreground mb-1 block text-xs font-medium"
                        >
                            Collection Date
                            <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model="collectionForm.collection_date"
                            type="date"
                            required
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                        />
                        <p
                            v-if="collectionForm.errors.collection_date"
                            class="text-destructive mt-1 text-xs"
                        >
                            {{ collectionForm.errors.collection_date }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="text-foreground mb-1 block text-xs font-medium"
                            >Notes / Batch Description</label
                        >
                        <textarea
                            v-model="collectionForm.notes"
                            rows="2"
                            placeholder="e.g. Week 2 student registration batch"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                        ></textarea>
                    </div>

                    <div
                        class="border-border flex justify-end gap-2 border-t pt-3"
                    >
                        <button
                            type="button"
                            @click="showCollectionModal = false"
                            class="border-border hover:bg-muted text-foreground rounded-md border px-4 py-2 text-xs font-medium transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="collectionForm.processing"
                            :class="buttonVariants({ variant: 'default' })"
                            class="bg-orange-500 text-white hover:bg-orange-600"
                        >
                            {{
                                collectionForm.processing
                                    ? 'Saving...'
                                    : 'Record Payment'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
