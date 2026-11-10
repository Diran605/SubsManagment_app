<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import { card, table } from '@/lib/ui';
import { formatDate, formatMoney, orDash } from '@/lib/format';
import businessesRoutes from '@/routes/businesses';
import subscriptionsRoutes from '@/routes/subscriptions';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    CalendarClock,
    Layers,
    Pencil,
    Plus,
    Trash2,
    Wallet,
} from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const can = (permission: string) =>
    page.props.auth?.user?.permissions?.includes(permission);
const canEdit = computed(() => can('edit businesses'));
const canDelete = computed(() => can('delete businesses'));
const canCreateSubscription = computed(() => can('create subscriptions'));

const props = defineProps<{
    business: any;
}>();

const subscriptions = computed<any[]>(() => props.business.subscriptions ?? []);
const running = computed(() =>
    subscriptions.value.filter((s) =>
        ['active', 'expiring_soon'].includes(s.status_badge),
    ),
);
const totalValue = computed(() =>
    subscriptions.value.reduce((sum, s) => sum + Number(s.amount || 0), 0),
);
const nextEnd = computed(
    () => running.value.map((s) => s.end_date as string).sort()[0] ?? null,
);

const contactRows = computed(() => [
    { label: 'Contact person', value: orDash(props.business.contact_person) },
    { label: 'Email', value: orDash(props.business.email) },
    { label: 'Phone', value: orDash(props.business.phone) },
    { label: 'Address', value: orDash(props.business.address) },
    { label: 'Landmark', value: orDash(props.business.landmark) },
]);

const deleteForm = useForm({});

const confirmAction = useConfirm();
const confirmDelete = async () => {
    if (
        await confirmAction({
            title: 'Delete this business?',
            description:
                'It disappears from lists, and its subscriptions stop counting towards revenue.',
            confirmText: 'Delete business',
            tone: 'danger',
        })
    ) {
        deleteForm.delete(businessesRoutes.destroy.url(props.business.id));
    }
};

const openSubscription = (id: number) =>
    router.visit(subscriptionsRoutes.show.url(id));
</script>

<template>
    <Head :title="business.name" />

    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="business.name"
            description="Client details and subscription history."
            :back-href="businessesRoutes.index.url()"
            back-label="Back to businesses"
        >
            <template #actions>
                <Link
                    v-if="canEdit"
                    :href="businessesRoutes.edit.url(business.id)"
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    <Pencil class="size-4" /> Edit
                </Link>
                <button
                    v-if="canDelete"
                    type="button"
                    @click="confirmDelete"
                    :class="buttonVariants({ variant: 'outline' })"
                    class="hover:border-destructive/40 hover:bg-destructive/10 hover:text-destructive"
                >
                    <Trash2 class="size-4" /> Delete
                </button>
                <Link
                    v-if="canCreateSubscription"
                    :href="
                        subscriptionsRoutes.create.url({
                            query: { business_id: business.id },
                        })
                    "
                    :class="buttonVariants({ variant: 'default' })"
                >
                    <Plus class="size-4" /> New subscription
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatCard
                label="Running subscriptions"
                :value="running.length"
                :hint="`${subscriptions.length} in total`"
                tone="primary"
            >
                <template #icon><Layers /></template>
            </StatCard>
            <StatCard
                label="Total contract value"
                :value="formatMoney(totalValue)"
                unit="FCFA"
                hint="All subscriptions, past and present"
                tone="accent"
            >
                <template #icon><Wallet /></template>
            </StatCard>
            <StatCard
                label="Next end date"
                :value="nextEnd ? formatDate(nextEnd) : '—'"
                :hint="
                    nextEnd
                        ? 'Earliest running subscription to end'
                        : 'Nothing running right now'
                "
                tone="warning"
            >
                <template #icon><CalendarClock /></template>
            </StatCard>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section :class="[card.base, 'h-fit']">
                <div :class="card.header">
                    <h2 :class="card.title">Contact</h2>
                </div>
                <dl class="divide-border divide-y text-sm">
                    <div
                        v-for="row in contactRows"
                        :key="row.label"
                        class="px-5 py-3"
                    >
                        <dt class="text-muted-foreground text-xs">
                            {{ row.label }}
                        </dt>
                        <dd class="text-foreground mt-0.5 wrap-break-word">
                            {{ row.value }}
                        </dd>
                    </div>
                    <div class="bg-muted/30 px-5 py-3">
                        <dt class="text-muted-foreground text-xs">Added by</dt>
                        <dd class="text-foreground mt-0.5">
                            {{ business.creator?.name ?? '—' }}
                            <span class="text-muted-foreground">
                                · {{ formatDate(business.created_at) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </section>

            <section :class="[card.base, 'lg:col-span-2']">
                <div :class="card.header">
                    <h2 :class="card.title">Subscriptions</h2>
                    <span class="text-muted-foreground text-xs">
                        Newest first
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead :class="table.head">
                            <tr>
                                <th :class="table.th">Service</th>
                                <th :class="table.th">Period</th>
                                <th :class="table.th">Status</th>
                                <th :class="[table.th, 'text-right']">
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-border divide-y">
                            <tr
                                v-for="sub in subscriptions"
                                :key="sub.id"
                                :class="[table.row, 'cursor-pointer']"
                                @click="openSubscription(sub.id)"
                            >
                                <td class="px-4 py-3">
                                    <div class="text-foreground font-medium">
                                        {{ sub.service?.name ?? '—' }}
                                    </div>
                                    <div
                                        class="mt-1 flex flex-wrap items-center gap-1"
                                    >
                                        <Badge
                                            v-if="sub.package"
                                            variant="info"
                                        >
                                            {{ sub.package.name }}
                                        </Badge>
                                        <span
                                            v-else
                                            class="text-muted-foreground text-xs"
                                            >Custom</span
                                        >
                                        <span
                                            class="text-muted-foreground text-xs capitalize"
                                            >· {{ sub.mode }}</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="text-muted-foreground px-4 py-3 whitespace-nowrap"
                                >
                                    {{ formatDate(sub.start_date) }} –
                                    {{ formatDate(sub.end_date) }}
                                </td>
                                <td class="px-4 py-3">
                                    <StatusBadge :status="sub.status_badge" />
                                </td>
                                <td
                                    class="text-foreground px-4 py-3 text-right font-medium whitespace-nowrap tabular-nums"
                                >
                                    {{ formatMoney(sub.amount) }}
                                    <span
                                        class="text-muted-foreground text-xs font-normal"
                                        >FCFA</span
                                    >
                                </td>
                            </tr>
                            <tr v-if="subscriptions.length === 0">
                                <td colspan="4" :class="table.empty">
                                    No subscriptions yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>
