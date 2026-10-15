<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';

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
    router.get(route('reports.index'), { period: selectedPeriod.value }, { preserveState: true });
};

import { Bar, Pie, Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement } from 'chart.js';
import { useAppearance } from '@/composables/useAppearance';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement);

const { resolvedAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

watchEffect(() => {
    ChartJS.defaults.color = isDark.value ? '#94a3b8' : '#475569';
    ChartJS.defaults.font.family = "'Instrument Sans', sans-serif";
});

const commonOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    layout: {
        padding: { top: 10, bottom: 10 }
    },
    scales: {
        x: {
            grid: {
                display: false,
                drawBorder: false,
            },
            ticks: {
                font: { size: 11 },
                autoSkip: false,
                callback: function(value: any) {
                    const label = this.getLabelForValue(value);
                    if (typeof label === 'string') {
                        const words = label.split(' ');
                        const lines = [];
                        let currentLine = '';
                        words.forEach(word => {
                            if ((currentLine + word).length > 12) {
                                if (currentLine.trim().length > 0) {
                                    lines.push(currentLine.trim());
                                }
                                currentLine = word + ' ';
                            } else {
                                currentLine += word + ' ';
                            }
                        });
                        if (currentLine.trim().length > 0) {
                            lines.push(currentLine.trim());
                        }
                        return lines;
                    }
                    return label;
                }
            }
        },
        y: {
            grid: {
                color: isDark.value ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
                drawBorder: false,
                borderDash: [5, 5],
            }
        }
    }
}));

const pieOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                color: isDark.value ? '#94a3b8' : '#475569'
            }
        }
    }
}));

const formatCurrency = (value: number) => {
    return Number(value).toLocaleString('en-US') + ' FCFA';
};

const getChartData = (data: Record<string, number>, label: string, color: string) => {
    return {
        labels: Object.keys(data),
        datasets: [{
            label: label,
            data: Object.values(data),
            backgroundColor: color,
            borderColor: color,
            borderWidth: 2,
            borderRadius: 4,
            maxBarThickness: 40,
            tension: 0.3,
            fill: false,
        }]
    };
};

const getPieChartData = (data: Record<string, number>) => {
    return {
        labels: Object.keys(data),
        datasets: [{
            data: Object.values(data),
            backgroundColor: [
                '#3b82f6', // blue-500
                '#10b981', // emerald-500
                '#f59e0b', // amber-500
                '#ef4444', // red-500
                '#8b5cf6', // violet-500
            ],
            borderColor: isDark.value ? '#020817' : '#ffffff',
            borderWidth: 2,
        }]
    };
};
</script>

<template>
    <Head title="Reports & Analytics" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Reports & Analytics</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Detailed breakdown of your subscription metrics.
                </p>
            </div>
            <div>
                <select v-model="selectedPeriod" @change="applyFilter" class="rounded-md border-input bg-background text-foreground shadow-sm focus:border-primary focus:ring-primary text-sm">
                    <option value="6m">Last 6 Months</option>
                    <option value="12m">Last 12 Months</option>
                    <option value="all">All Time</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <p class="text-sm font-medium text-muted-foreground">Active Revenue</p>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ formatCurrency(props.metrics.totalActiveRevenue) }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <p class="text-sm font-medium text-muted-foreground">Total Historical Revenue</p>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ formatCurrency(props.metrics.totalHistoricalRevenue) }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <p class="text-sm font-medium text-muted-foreground">New Subs Revenue</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(props.metrics.newRevenue) }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <p class="text-sm font-medium text-muted-foreground">Renewals Revenue</p>
                <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(props.metrics.renewedRevenue) }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <p class="text-sm font-medium text-muted-foreground">Renewal Rate</p>
                <p class="mt-2 text-2xl font-bold text-violet-600 dark:text-violet-400">{{ props.metrics.renewalRate }}%</p>
            </div>
        </div>

        <div class="rounded-2xl border border-border bg-card shadow-sm flex flex-col mb-2">
            <div class="border-b border-border p-4 bg-muted/20">
                <h3 class="font-semibold text-foreground">Revenue Over Time</h3>
            </div>
            <div class="p-4 flex-1 h-72">
                <Line v-if="Object.keys(props.charts.revenueOverTime).length" :data="getChartData(props.charts.revenueOverTime, 'Revenue', isDark ? '#a78bfa' : '#8b5cf6')" :options="commonOptions" />
                <p v-else class="text-sm text-muted-foreground text-center mt-10">No data</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
            <div class="rounded-2xl border border-border bg-card shadow-sm flex flex-col lg:col-span-2">
                <div class="border-b border-border p-4 bg-muted/20">
                    <h3 class="font-semibold text-foreground">Revenue by Service</h3>
                </div>
                <div class="p-4 flex-1 h-80">
                    <Bar v-if="Object.keys(props.charts.revenueByService).length" :data="getChartData(props.charts.revenueByService, 'Revenue', isDark ? '#60a5fa' : '#3b82f6')" :options="commonOptions" />
                    <p v-else class="text-sm text-muted-foreground">No data</p>
                </div>
            </div>

            <div class="rounded-2xl border border-border bg-card shadow-sm flex flex-col">
                <div class="border-b border-border p-4 bg-muted/20">
                    <h3 class="font-semibold text-foreground">Revenue by Mode</h3>
                </div>
                <div class="p-4 flex-1 h-80 flex items-center justify-center">
                    <Pie v-if="Object.keys(props.charts.revenueByMode).length" :data="getPieChartData(props.charts.revenueByMode)" :options="pieOptions" />
                    <p v-else class="text-sm text-muted-foreground">No data</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div class="rounded-2xl border border-border bg-card shadow-sm flex flex-col">
                <div class="border-b border-border p-4 bg-muted/20">
                    <h3 class="font-semibold text-foreground">Top 10 Businesses</h3>
                </div>
                <div class="p-4 flex-1 h-80">
                    <Bar v-if="Object.keys(props.charts.revenueByBusiness).length" :data="getChartData(props.charts.revenueByBusiness, 'Revenue', isDark ? '#34d399' : '#10b981')" :options="commonOptions" />
                    <p v-else class="text-sm text-muted-foreground">No data</p>
                </div>
            </div>
        </div>
    </div>
</template>
