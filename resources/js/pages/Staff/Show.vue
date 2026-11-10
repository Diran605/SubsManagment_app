<script setup lang="ts">
import staffRoutes from '@/routes/staff';
import businessesRoutes from '@/routes/businesses';
import subscriptionsRoutes from '@/routes/subscriptions';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Building2, CalendarDays, Layers, Pencil, Power } from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { buttonVariants } from '@/components/ui/button';
import { card, table } from '@/lib/ui';
import { formatDate, formatMoney } from '@/lib/format';

const props = defineProps<{
    staff: any;
}>();

const businesses = computed<any[]>(() => props.staff.businesses ?? []);
const subscriptions = computed<any[]>(() => props.staff.subscriptions ?? []);
</script>

<template>
    <Head :title="staff.name" />

    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="staff.name"
            :back-href="staffRoutes.index.url()"
            back-label="Back to staff"
        >
            <template #description>
                <span class="inline-flex flex-wrap items-center gap-2">
                    {{ staff.email }}
                    <Badge :variant="staff.is_active ? 'success' : 'muted'">
                        {{ staff.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </span>
            </template>
            <template #actions>
                <Link
                    :href="staffRoutes.toggle.url(staff.id)"
                    method="patch"
                    as="button"
                    :class="buttonVariants({ variant: 'outline' })"
                    class="hover:border-warning/40 hover:bg-warning/10 hover:text-warning"
                >
                    <Power class="size-4" />
                    {{ staff.is_active ? 'Deactivate' : 'Activate' }}
                </Link>
                <Link
                    :href="staffRoutes.edit.url(staff.id)"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    <Pencil class="size-4" /> Edit
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatCard
                label="Businesses added"
                :value="businesses.length"
                tone="primary"
            >
                <template #icon><Building2 /></template>
            </StatCard>
            <StatCard
                label="Subscriptions recorded"
                :value="subscriptions.length"
                tone="accent"
            >
                <template #icon><Layers /></template>
            </StatCard>
            <StatCard
                label="Member since"
                :value="formatDate(staff.created_at)"
                tone="info"
            >
                <template #icon><CalendarDays /></template>
            </StatCard>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section :class="[card.base, 'h-fit']">
                <div :class="card.header">
                    <h2 :class="card.title">Businesses added</h2>
                </div>
                <ul class="divide-border divide-y text-sm">
                    <li
                        v-for="b in businesses"
                        :key="b.id"
                        class="flex items-center justify-between gap-3 px-5 py-3"
                    >
                        <Link
                            :href="businessesRoutes.show.url(b.id)"
                            class="text-foreground hover:text-primary truncate font-medium"
                            >{{ b.name }}</Link
                        >
                        <span class="text-muted-foreground shrink-0 text-xs">{{
                            formatDate(b.created_at)
                        }}</span>
                    </li>
                    <li v-if="businesses.length === 0" :class="table.empty">
                        None yet.
                    </li>
                </ul>
            </section>

            <section :class="[card.base, 'lg:col-span-2']">
                <div :class="card.header">
                    <h2 :class="card.title">Subscriptions recorded</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead :class="table.head">
                            <tr>
                                <th :class="table.th">Client</th>
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
                                :class="table.row"
                            >
                                <td class="px-4 py-3">
                                    <Link
                                        :href="
                                            subscriptionsRoutes.show.url(sub.id)
                                        "
                                        class="text-foreground hover:text-primary font-medium"
                                    >
                                        {{ sub.business?.name ?? '—' }}
                                    </Link>
                                    <div class="text-muted-foreground text-xs">
                                        {{ sub.service?.name ?? '—' }}
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
                                    None yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>
