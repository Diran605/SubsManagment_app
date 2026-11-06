<script setup lang="ts">
import subscriptionsRoutes from '@/routes/subscriptions';
import businessesRoutes from '@/routes/businesses';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';
import { buttonVariants } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import { rowActionVariants } from '@/lib/ui';
import { AlertTriangle, Banknote, Building2, Layers, Plus, Smartphone } from '@lucide/vue';
import { useAppearance } from '@/composables/useAppearance';
import { wrappedTickCallback } from '@/lib/charts';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps<{
    canViewSubscriptions: boolean;
    expiringSubscriptions: any[];
    expiringThreshold: number;
    totalRevenue: number | string;
    momo: { count: number; target: number; collected: number };
    subscriptionsByService: Record<string, number>;
    subscriptionsByBusiness: Record<string, number>;
    totalBusinesses: number;
    totalServices: number;
}>();

const page = usePage<any>();
const can = (permission: string) =>
    (page.props.auth?.user?.permissions || []).includes(permission);

const { resolvedAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

watchEffect(() => {
    ChartJS.defaults.color = isDark.value ? '#94a3b8' : '#475569';
    ChartJS.defaults.font.family = "'Instrument Sans', sans-serif";
});

const getBarChartData = (
    data: Record<string, number>,
    color: { light: string; dark: string },
    dark: boolean,
) => ({
    labels: Object.keys(data),
    datasets: [
        {
            label: 'Active Subscriptions',
            data: Object.values(data),
            backgroundColor: dark ? color.dark : color.light,
            borderRadius: 4,
            maxBarThickness: 40,
        },
    ],
});

const momoPercent = computed(() =>
    props.momo.target > 0
        ? Math.min(100, Math.round((props.momo.collected / props.momo.target) * 100))
        : 0,
);

const serviceColor = { light: '#0A3094', dark: '#0ea5e9' };
const businessColor = { light: '#FF5F00', dark: '#f97316' };

// Give each bar at least 80px so long label lists scroll instead of squashing.
const minChartWidth = (data: Record<string, number>, threshold: number) => {
    const count = Object.keys(data || {}).length;
    return count > threshold ? `${count * 80}px` : '100%';
};

const barChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: {
                font: { size: 11 },
                autoSkip: false,
                callback: wrappedTickCallback,
            },
        },
        y: {
            grid: {
                color: isDark.value ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
            },
            ticks: {
                stepSize: 1,
                padding: 10,
                font: { size: 12 },
            },
        },
    },
}));
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4 md:p-8">
        <PageHeader title="Dashboard" description="Active subscriptions and revenue at a glance.">
            <template #actions>
                <Link v-if="can('create businesses')" :href="businessesRoutes.create.url()" :class="buttonVariants({ variant: 'outline' })">
                    Add business
                </Link>
                <Link v-if="can('create subscriptions')" :href="subscriptionsRoutes.create.url()" :class="buttonVariants()">
                    <Plus class="size-4" /> New subscription
                </Link>
            </template>
        </PageHeader>

        <div
            v-if="!props.canViewSubscriptions"
            class="rounded-xl border border-border bg-card p-6 text-sm text-muted-foreground shadow-xs"
        >
            Your account does not have access to subscription data. Ask an administrator if you need it.
        </div>

        <template v-else>
            <!-- Expiring soon alert -->
            <div
                v-if="props.expiringSubscriptions.length > 0"
                role="status"
                class="flex flex-col gap-3 rounded-xl border border-warning/30 bg-warning/10 p-4 sm:flex-row sm:items-center"
            >
                <AlertTriangle class="size-5 shrink-0 text-warning" aria-hidden="true" />
                <div class="flex-1 text-sm">
                    <p class="font-medium text-foreground">Renewals coming up</p>
                    <p class="text-muted-foreground">
                        {{ props.expiringSubscriptions.length }} subscription(s) end within the next {{ props.expiringThreshold }} days.
                    </p>
                </div>
                <Link
                    :href="subscriptionsRoutes.index.url({ query: { status: 'expiring_soon' } })"
                    :class="rowActionVariants({ tone: 'warning' })"
                >
                    Review them
                </Link>
            </div>

            <!-- Metrics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" :class="props.momo.count > 0 ? 'xl:grid-cols-4' : 'xl:grid-cols-3'">
                <StatCard
                    label="Active revenue"
                    :value="Number(props.totalRevenue).toLocaleString('en-US')"
                    unit="FCFA"
                    hint="School payments plus MoMo actually collected"
                    tone="accent"
                >
                    <template #icon><Banknote /></template>
                </StatCard>

                <StatCard
                    v-if="props.momo.count > 0"
                    label="Student MoMo collected"
                    :value="Number(props.momo.collected).toLocaleString('en-US')"
                    unit="FCFA"
                    tone="info"
                >
                    <template #icon><Smartphone /></template>
                    <div
                        class="h-1.5 w-full overflow-hidden rounded-full bg-muted"
                        role="progressbar"
                        :aria-valuenow="momoPercent"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-label="MoMo collected against target"
                    >
                        <div class="h-full rounded-full bg-info transition-[width]" :style="{ width: momoPercent + '%' }" />
                    </div>
                    <p class="mt-2">
                        {{ momoPercent }}% of {{ Number(props.momo.target).toLocaleString('en-US') }} FCFA target ·
                        {{ props.momo.count }} subscription(s)
                    </p>
                </StatCard>

                <StatCard label="Businesses" :value="props.totalBusinesses" hint="Clients on the platform" tone="primary">
                    <template #icon><Building2 /></template>
                </StatCard>

                <StatCard label="Services" :value="props.totalServices" hint="Available to subscribe to" tone="primary">
                    <template #icon><Layers /></template>
                </StatCard>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- Subscriptions by service -->
                <section class="flex flex-col rounded-xl border border-border bg-card p-5 shadow-xs lg:col-span-2">
                    <h2 class="text-base font-semibold text-foreground">Active subscriptions by service</h2>
                    <div class="mt-4 overflow-x-auto">
                        <div class="h-56" :style="{ minWidth: minChartWidth(props.subscriptionsByService, 5) }">
                            <Bar
                                v-if="Object.keys(props.subscriptionsByService || {}).length"
                                :data="getBarChartData(props.subscriptionsByService, serviceColor, isDark)"
                                :options="barChartOptions"
                            />
                            <p v-else class="text-sm text-muted-foreground">No active subscriptions yet.</p>
                        </div>
                    </div>
                </section>

                <!-- Expiring soon -->
                <section class="flex max-h-80 flex-col rounded-xl border border-border bg-card p-5 shadow-xs">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-foreground">Expiring soon</h2>
                        <span class="rounded-full bg-warning/10 px-2 py-0.5 text-xs font-semibold text-warning tabular-nums">
                            {{ props.expiringSubscriptions.length }}
                        </span>
                    </div>

                    <ul class="-mx-1 flex-1 space-y-1 overflow-y-auto px-1">
                        <li
                            v-for="sub in props.expiringSubscriptions"
                            :key="sub.id"
                            class="flex items-center justify-between gap-3 rounded-lg p-2.5 transition-colors hover:bg-muted/60"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-foreground">{{ sub.business?.name }}</p>
                                <p class="truncate text-xs text-muted-foreground">
                                    {{ sub.service?.name }} · ends {{ new Date(sub.end_date).toLocaleDateString() }}
                                </p>
                            </div>
                            <Link
                                v-if="can('create subscriptions')"
                                :href="subscriptionsRoutes.createRenewal.url(sub.id)"
                                :class="rowActionVariants({ tone: 'success' })"
                            >Renew</Link>
                        </li>
                        <li v-if="props.expiringSubscriptions.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                            Nothing expiring soon.
                        </li>
                    </ul>
                </section>
            </div>

            <!-- Subscriptions by business -->
            <section class="rounded-xl border border-border bg-card p-5 shadow-xs">
                <h2 class="text-base font-semibold text-foreground">Top businesses by active subscriptions</h2>
                <div class="mt-4 overflow-x-auto">
                    <div class="h-80" :style="{ minWidth: minChartWidth(props.subscriptionsByBusiness, 7) }">
                        <Bar
                            v-if="Object.keys(props.subscriptionsByBusiness || {}).length"
                            :data="getBarChartData(props.subscriptionsByBusiness, businessColor, isDark)"
                            :options="barChartOptions"
                        />
                        <p v-else class="text-sm text-muted-foreground">No active subscriptions yet.</p>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>
