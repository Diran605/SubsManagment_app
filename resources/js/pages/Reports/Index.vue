<script setup lang="ts">
import reportsRoutes from '@/routes/reports';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Filler,
} from 'chart.js';
import { Banknote, History, RefreshCw, Repeat, Sparkles } from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import { useAppearance } from '@/composables/useAppearance';
import { wrappedTickCallback } from '@/lib/charts';
import { fieldClass } from '@/lib/ui';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    Filler,
);

const props = defineProps<{
    period: string;
    metrics: {
        totalHistoricalRevenue: number;
        totalActiveRevenue: number;
        newRevenue: number;
        renewedRevenue: number;
        renewalRate: number;
    };
    charts: {
        revenueOverTime: Record<string, number>;
        revenueByService: Record<string, number>;
        revenueByBusiness: Record<string, number>;
        revenueByMode: Record<string, number>;
    };
}>();

const selectedPeriod = ref(props.period);

const applyFilter = () => {
    router.get(
        reportsRoutes.index.url(),
        { period: selectedPeriod.value },
        { preserveState: true },
    );
};

const { resolvedAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

watchEffect(() => {
    ChartJS.defaults.color = isDark.value ? '#94a3b8' : '#475569';
    ChartJS.defaults.font.family = "'Instrument Sans', sans-serif";
});

// Brand palette, matching the dashboard charts.
const palette = computed(() =>
    isDark.value
        ? {
              navy: '#0ea5e9',
              orange: '#f97316',
              sky: '#7dd3fc',
              muted: '#475569',
          }
        : {
              navy: '#0A3094',
              orange: '#FF5F00',
              sky: '#60a5fa',
              muted: '#94a3b8',
          },
);

const money = (value: number) => Number(value).toLocaleString('en-US');
const compact = new Intl.NumberFormat('en-US', {
    notation: 'compact',
    maximumFractionDigits: 1,
});

const hasData = (data: Record<string, number>) =>
    Object.keys(data || {}).length > 0;

// Give each bar at least 80px so long label lists scroll instead of squashing.
const minChartWidth = (data: Record<string, number>, threshold: number) => {
    const count = Object.keys(data || {}).length;
    return count > threshold ? `${count * 80}px` : '100%';
};

const axisOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: { parsed: { y: number | null } }) =>
                    ` ${money(ctx.parsed.y ?? 0)} FCFA`,
            },
        },
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
            beginAtZero: true,
            border: { display: false },
            grid: {
                color: isDark.value
                    ? 'rgba(255, 255, 255, 0.06)'
                    : 'rgba(0, 0, 0, 0.06)',
            },
            ticks: {
                font: { size: 11 },
                callback: (value: number | string) =>
                    compact.format(Number(value)),
            },
        },
    },
}));

const lineOptions = computed(() => ({
    ...axisOptions.value,
    scales: {
        ...axisOptions.value.scales,
        // Many months: let Chart.js thin out the labels instead of wrapping them all.
        x: {
            grid: { display: false },
            ticks: { font: { size: 11 }, autoSkip: true, maxRotation: 0 },
        },
    },
}));

const doughnutOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '62%',
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 16,
                color: isDark.value ? '#94a3b8' : '#475569',
            },
        },
        tooltip: {
            callbacks: {
                label: (ctx: { label: string; parsed: number }) =>
                    ` ${ctx.label}: ${money(ctx.parsed)} FCFA`,
            },
        },
    },
}));

const barData = (data: Record<string, number>, color: string) => ({
    labels: Object.keys(data),
    datasets: [
        {
            label: 'Revenue',
            data: Object.values(data),
            backgroundColor: color,
            borderRadius: 4,
            maxBarThickness: 36,
        },
    ],
});

const lineData = computed(() => ({
    labels: Object.keys(props.charts.revenueOverTime),
    datasets: [
        {
            label: 'Revenue',
            data: Object.values(props.charts.revenueOverTime),
            borderColor: palette.value.navy,
            backgroundColor: palette.value.navy + '1A', // ~10% opacity fill
            fill: true,
            tension: 0, // straight lines: don't invent values between months
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: palette.value.navy,
        },
    ],
}));

const modeData = computed(() => ({
    labels: Object.keys(props.charts.revenueByMode).map(
        (m) => m.charAt(0).toUpperCase() + m.slice(1),
    ),
    datasets: [
        {
            data: Object.values(props.charts.revenueByMode),
            backgroundColor: [
                palette.value.navy,
                palette.value.orange,
                palette.value.sky,
                palette.value.muted,
            ],
            borderColor: isDark.value ? '#0f172a' : '#ffffff',
            borderWidth: 2,
        },
    ],
}));
</script>

<template>
    <Head title="Reports & Analytics" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <PageHeader
            title="Reports & Analytics"
            description="Revenue from subscriptions started in the selected period."
        >
            <template #actions>
                <select
                    v-model="selectedPeriod"
                    @change="applyFilter"
                    aria-label="Reporting period"
                    :class="fieldClass"
                >
                    <option value="6m">Last 6 months</option>
                    <option value="12m">Last 12 months</option>
                    <option value="all">All time</option>
                </select>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <StatCard
                label="Active revenue"
                :value="money(props.metrics.totalActiveRevenue)"
                unit="FCFA"
                hint="Subscriptions still running"
                tone="accent"
            >
                <template #icon><Banknote /></template>
            </StatCard>
            <StatCard
                label="Total revenue"
                :value="money(props.metrics.totalHistoricalRevenue)"
                unit="FCFA"
                hint="Including expired subscriptions"
                tone="primary"
            >
                <template #icon><History /></template>
            </StatCard>
            <StatCard
                label="From new subscriptions"
                :value="money(props.metrics.newRevenue)"
                unit="FCFA"
                hint="Active, first-time clients"
                tone="primary"
            >
                <template #icon><Sparkles /></template>
            </StatCard>
            <StatCard
                label="From renewals"
                :value="money(props.metrics.renewedRevenue)"
                unit="FCFA"
                hint="Active, renewed subscriptions"
                tone="primary"
            >
                <template #icon><RefreshCw /></template>
            </StatCard>
            <StatCard
                label="Renewal rate"
                :value="props.metrics.renewalRate"
                unit="%"
                hint="Share of active subscriptions that are renewals"
                tone="success"
            >
                <template #icon><Repeat /></template>
            </StatCard>
        </div>

        <section class="border-border bg-card rounded-xl border p-5 shadow-xs">
            <h2 class="text-foreground text-base font-semibold">
                Revenue over time
            </h2>
            <p class="text-muted-foreground mt-0.5 text-xs">
                School payments by start month; MoMo money by the month it was
                collected.
            </p>
            <div class="mt-4 h-72">
                <Line
                    v-if="hasData(props.charts.revenueOverTime)"
                    :data="lineData"
                    :options="lineOptions"
                />
                <p
                    v-else
                    class="text-muted-foreground pt-10 text-center text-sm"
                >
                    No revenue in this period.
                </p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <section
                class="border-border bg-card rounded-xl border p-5 shadow-xs lg:col-span-2"
            >
                <h2 class="text-foreground text-base font-semibold">
                    Revenue by service
                </h2>
                <div class="mt-4 overflow-x-auto">
                    <div
                        class="h-72"
                        :style="{
                            minWidth: minChartWidth(
                                props.charts.revenueByService,
                                6,
                            ),
                        }"
                    >
                        <Bar
                            v-if="hasData(props.charts.revenueByService)"
                            :data="
                                barData(
                                    props.charts.revenueByService,
                                    palette.navy,
                                )
                            "
                            :options="axisOptions"
                        />
                        <p
                            v-else
                            class="text-muted-foreground pt-10 text-center text-sm"
                        >
                            No active revenue yet.
                        </p>
                    </div>
                </div>
            </section>

            <section
                class="border-border bg-card rounded-xl border p-5 shadow-xs"
            >
                <h2 class="text-foreground text-base font-semibold">
                    Revenue by mode
                </h2>
                <div class="mt-4 h-72">
                    <Doughnut
                        v-if="hasData(props.charts.revenueByMode)"
                        :data="modeData"
                        :options="doughnutOptions"
                    />
                    <p
                        v-else
                        class="text-muted-foreground pt-10 text-center text-sm"
                    >
                        No active revenue yet.
                    </p>
                </div>
            </section>
        </div>

        <section class="border-border bg-card rounded-xl border p-5 shadow-xs">
            <h2 class="text-foreground text-base font-semibold">
                Top businesses by active revenue
            </h2>
            <div class="mt-4 overflow-x-auto">
                <div
                    class="h-72"
                    :style="{
                        minWidth: minChartWidth(
                            props.charts.revenueByBusiness,
                            8,
                        ),
                    }"
                >
                    <Bar
                        v-if="hasData(props.charts.revenueByBusiness)"
                        :data="
                            barData(
                                props.charts.revenueByBusiness,
                                palette.orange,
                            )
                        "
                        :options="axisOptions"
                    />
                    <p
                        v-else
                        class="text-muted-foreground pt-10 text-center text-sm"
                    >
                        No active revenue yet.
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>
