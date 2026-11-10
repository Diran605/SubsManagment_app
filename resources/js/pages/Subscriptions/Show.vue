<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import subscriptionsRoutes from '@/routes/subscriptions';
import businessesRoutes from '@/routes/businesses';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    CalendarRange,
    Pencil,
    Plus,
    RefreshCw,
    RotateCcw,
    Smartphone,
    Trash2,
    Wallet,
} from '@lucide/vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button, buttonVariants } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { today } from '@/lib/dates';
import { formatDate, formatMoney } from '@/lib/format';
import { card, rowActionVariants, table, textareaClass } from '@/lib/ui';

const page = usePage<any>();
const can = (permission: string) =>
    page.props.auth?.user?.permissions?.includes(permission);
const canCreate = computed(() => can('create subscriptions'));
const canEdit = computed(() => can('edit subscriptions'));
const canRestore = computed(() => can('restore subscriptions'));

const props = defineProps<{
    subscription: any;
}>();

const sub = computed(() => props.subscription);
const isTrashed = computed(() => sub.value.status_badge === 'trashed');
const canRenew = computed(
    () =>
        canCreate.value &&
        !isTrashed.value &&
        !['active', 'renewed'].includes(sub.value.status_badge),
);

const isStudentMoMo = computed(
    () => sub.value.package?.payer_type === 'student_momo',
);

const collections = computed<any[]>(() => sub.value.collections ?? []);
const totalCollected = computed(() =>
    collections.value.reduce(
        (acc: number, c: any) => acc + parseFloat(c.amount || 0),
        0,
    ),
);

const targetAmount = computed(() => parseFloat(sub.value.amount || 0));
const remainingAmount = computed(() =>
    Math.max(0, targetAmount.value - totalCollected.value),
);
const collectionPercent = computed(() =>
    targetAmount.value > 0
        ? Math.min(
              100,
              Math.round((totalCollected.value / targetAmount.value) * 100),
          )
        : 0,
);

const details = computed(
    () =>
        [
            { label: 'Mode', value: sub.value.mode, capitalize: true },
            {
                label: 'Package',
                value: sub.value.package?.name ?? 'Custom',
            },
            sub.value.package?.pricing_model === 'per_student'
                ? {
                      label: 'Price per student',
                      value: `${formatMoney(sub.value.package.price)} FCFA`,
                  }
                : null,
            sub.value.student_count
                ? {
                      label: 'Enrolled students',
                      value: Number(sub.value.student_count).toLocaleString(
                          'en-US',
                      ),
                  }
                : null,
            { label: 'Start date', value: formatDate(sub.value.start_date) },
            { label: 'End date', value: formatDate(sub.value.end_date) },
        ].filter(Boolean) as {
            label: string;
            value: string;
            capitalize?: boolean;
        }[],
);

// ── MoMo collections ─────────────────────────────────────────────────────
const showCollectionModal = ref(false);
const collectionForm = useForm({
    amount: '' as number | '',
    collection_date: today(),
    notes: '',
});

const submitCollection = () => {
    collectionForm.post(
        subscriptionsRoutes.collections.store.url(sub.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showCollectionModal.value = false;
                collectionForm.reset();
                collectionForm.collection_date = today();
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
        router.post(subscriptionsRoutes.restore.url(sub.value.id));
    }
};
</script>

<template>
    <Head
        :title="`${sub.service?.name ?? 'Subscription'} · ${sub.business?.name ?? ''}`"
    />

    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="sub.service?.name ?? 'Subscription'"
            :back-href="subscriptionsRoutes.index.url()"
            back-label="Back to subscriptions"
        >
            <template #description>
                <span class="inline-flex flex-wrap items-center gap-2">
                    <span>
                        for
                        <Link
                            :href="businessesRoutes.show.url(sub.business_id)"
                            class="text-foreground hover:text-primary font-medium"
                            >{{ sub.business?.name ?? '—' }}</Link
                        >
                    </span>
                    <StatusBadge :status="sub.status_badge" />
                    <Badge v-if="isStudentMoMo" variant="warning">
                        Student-funded (MoMo)
                    </Badge>
                </span>
            </template>
            <template #actions>
                <template v-if="!isTrashed">
                    <Link
                        v-if="canEdit"
                        :href="subscriptionsRoutes.edit.url(sub.id)"
                        :class="buttonVariants({ variant: 'outline' })"
                    >
                        <Pencil class="size-4" /> Edit
                    </Link>
                    <Link
                        v-if="canRenew"
                        :href="subscriptionsRoutes.createRenewal.url(sub.id)"
                        :class="buttonVariants({ variant: 'default' })"
                    >
                        <RefreshCw class="size-4" /> Renew
                    </Link>
                </template>
                <Button
                    v-else-if="canRestore"
                    variant="default"
                    @click="restoreSubscription"
                >
                    <RotateCcw class="size-4" /> Restore
                </Button>
            </template>
        </PageHeader>

        <div
            v-if="isTrashed"
            class="border-border bg-muted/40 text-muted-foreground rounded-lg border px-4 py-3 text-sm"
        >
            This subscription was deleted. It doesn't count towards revenue
            until it's restored.
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatCard
                :label="isStudentMoMo ? 'Collection target' : 'Contract amount'"
                :value="formatMoney(targetAmount)"
                unit="FCFA"
                :hint="
                    isStudentMoMo
                        ? 'Paid by students; only collected money counts as revenue'
                        : 'Paid by the client'
                "
                tone="accent"
            >
                <template #icon><Wallet /></template>
            </StatCard>
            <StatCard
                label="Period"
                :value="formatDate(sub.start_date)"
                :hint="`Ends ${formatDate(sub.end_date)}`"
                tone="primary"
            >
                <template #icon><CalendarRange /></template>
            </StatCard>
            <StatCard
                v-if="isStudentMoMo"
                label="Collected so far"
                :value="formatMoney(totalCollected)"
                unit="FCFA"
                tone="success"
            >
                <template #icon><Smartphone /></template>
                <div class="space-y-1.5">
                    <div
                        class="bg-success/15 h-1.5 overflow-hidden rounded-full"
                    >
                        <div
                            class="bg-success h-full rounded-full transition-all"
                            :style="{ width: `${collectionPercent}%` }"
                        />
                    </div>
                    <div class="flex justify-between">
                        <span>{{ collectionPercent }}% of target</span>
                        <span
                            >{{ formatMoney(remainingAmount) }} FCFA to go</span
                        >
                    </div>
                </div>
            </StatCard>
            <StatCard
                v-else
                label="Modules"
                :value="sub.modules?.length ?? 0"
                :hint="
                    sub.package
                        ? `Package: ${sub.package.name}`
                        : 'Custom selection'
                "
                tone="info"
            />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <!-- MoMo collections -->
                <section v-if="isStudentMoMo" :class="card.base">
                    <div :class="card.header">
                        <div>
                            <h2 :class="card.title">MoMo collections</h2>
                            <p class="text-muted-foreground text-xs">
                                Student Mobile Money payments received so far.
                            </p>
                        </div>
                        <Button
                            v-if="canEdit && !isTrashed"
                            size="sm"
                            @click="showCollectionModal = true"
                        >
                            <Plus class="size-4" /> Log payment
                        </Button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead :class="table.head">
                                <tr>
                                    <th :class="table.th">Date</th>
                                    <th :class="[table.th, 'text-right']">
                                        Amount
                                    </th>
                                    <th :class="table.th">Notes</th>
                                    <th :class="table.th">Recorded by</th>
                                    <th
                                        v-if="canEdit"
                                        :class="[table.th, 'text-right']"
                                    >
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-border divide-y">
                                <tr
                                    v-for="col in collections"
                                    :key="col.id"
                                    :class="table.row"
                                >
                                    <td
                                        class="text-foreground px-4 py-3 whitespace-nowrap"
                                    >
                                        {{ formatDate(col.collection_date) }}
                                    </td>
                                    <td
                                        class="text-success px-4 py-3 text-right font-semibold whitespace-nowrap tabular-nums"
                                    >
                                        {{ formatMoney(col.amount) }}
                                        <span class="text-xs font-normal"
                                            >FCFA</span
                                        >
                                    </td>
                                    <td class="text-muted-foreground px-4 py-3">
                                        {{ col.notes || '—' }}
                                    </td>
                                    <td class="text-muted-foreground px-4 py-3">
                                        {{ col.recorder?.name ?? '—' }}
                                    </td>
                                    <td
                                        v-if="canEdit"
                                        class="px-4 py-3 text-right"
                                    >
                                        <button
                                            type="button"
                                            :class="
                                                rowActionVariants({
                                                    tone: 'danger',
                                                })
                                            "
                                            @click="deleteCollection(col.id)"
                                        >
                                            <Trash2 class="mr-1 size-3.5" />
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="collections.length === 0">
                                    <td
                                        :colspan="canEdit ? 5 : 4"
                                        :class="table.empty"
                                    >
                                        No payments logged yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Modules -->
                <section :class="card.base">
                    <div :class="card.header">
                        <h2 :class="card.title">Included modules</h2>
                    </div>
                    <div :class="card.body">
                        <div
                            v-if="sub.modules?.length"
                            class="flex flex-wrap gap-2"
                        >
                            <Badge
                                v-for="mod in sub.modules"
                                :key="mod.id"
                                variant="info"
                            >
                                {{ mod.name }}
                            </Badge>
                        </div>
                        <p v-else class="text-muted-foreground text-sm">
                            No modules selected.
                        </p>
                        <p
                            v-if="sub.package?.description"
                            class="text-muted-foreground mt-4 text-sm"
                        >
                            {{ sub.package.description }}
                        </p>
                    </div>
                </section>

                <section v-if="sub.notes" :class="card.base">
                    <div :class="card.header">
                        <h2 :class="card.title">Notes</h2>
                    </div>
                    <p
                        :class="card.body"
                        class="text-foreground text-sm whitespace-pre-wrap"
                    >
                        {{ sub.notes }}
                    </p>
                </section>
            </div>

            <!-- Details -->
            <section :class="[card.base, 'h-fit']">
                <div :class="card.header">
                    <h2 :class="card.title">Details</h2>
                </div>
                <dl class="divide-border divide-y text-sm">
                    <div
                        v-for="row in details"
                        :key="row.label"
                        class="flex justify-between gap-4 px-5 py-3"
                    >
                        <dt class="text-muted-foreground">{{ row.label }}</dt>
                        <dd
                            class="text-foreground text-right font-medium"
                            :class="{ capitalize: row.capitalize }"
                        >
                            {{ row.value }}
                        </dd>
                    </div>
                </dl>
                <dl
                    class="border-border bg-muted/30 divide-border divide-y border-t text-xs"
                >
                    <div class="flex justify-between gap-4 px-5 py-2.5">
                        <dt class="text-muted-foreground">Added by</dt>
                        <dd class="text-foreground">
                            {{ sub.creator?.name ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 px-5 py-2.5">
                        <dt class="text-muted-foreground">Created</dt>
                        <dd class="text-foreground">
                            {{ formatDate(sub.created_at) }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 px-5 py-2.5">
                        <dt class="text-muted-foreground">Last updated</dt>
                        <dd class="text-foreground">
                            {{ formatDate(sub.updated_at) }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        <!-- Log MoMo payment -->
        <Dialog v-model:open="showCollectionModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Log MoMo payment</DialogTitle>
                    <DialogDescription>
                        Record money received from students via Mobile Money.
                    </DialogDescription>
                </DialogHeader>
                <form
                    id="collection-form"
                    @submit.prevent="submitCollection"
                    class="space-y-4"
                >
                    <FormField
                        label="Amount received (FCFA)"
                        for="amount"
                        required
                        :error="collectionForm.errors.amount"
                    >
                        <Input
                            id="amount"
                            v-model.number="collectionForm.amount"
                            type="number"
                            min="1"
                            step="0.01"
                            required
                            placeholder="e.g. 500000"
                        />
                    </FormField>
                    <FormField
                        label="Date received"
                        for="collection_date"
                        required
                        :error="collectionForm.errors.collection_date"
                    >
                        <Input
                            id="collection_date"
                            v-model="collectionForm.collection_date"
                            type="date"
                            required
                        />
                    </FormField>
                    <FormField
                        label="Notes"
                        for="notes"
                        :error="collectionForm.errors.notes"
                    >
                        <textarea
                            id="notes"
                            v-model="collectionForm.notes"
                            rows="2"
                            placeholder="e.g. Week 2 registration batch"
                            :class="textareaClass"
                        ></textarea>
                    </FormField>
                </form>
                <DialogFooter class="gap-2">
                    <Button
                        variant="outline"
                        type="button"
                        @click="showCollectionModal = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        form="collection-form"
                        :disabled="collectionForm.processing"
                    >
                        {{
                            collectionForm.processing
                                ? 'Saving…'
                                : 'Record payment'
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
