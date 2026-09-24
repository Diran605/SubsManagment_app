<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    subscriptions: any;
    expiringSubscriptions: any[];
    totalRevenue: number | string;
    revenueByService: Record<string, number>;
    subscriptionsByService: Record<string, number>;
    totalBusinesses: number;
    totalServices: number;
    filters: {
        search?: string;
        status?: string;
    };
}>();

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');

watch([search, status], ([newSearch, newStatus]) => {
    router.get(
        route('dashboard'),
        { search: newSearch, status: newStatus },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

import { useForm } from '@inertiajs/vue3';
const deleteForm = useForm({});
const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this subscription?')) {
        deleteForm.delete(route('subscriptions.destroy', id), {
            preserveScroll: true
        });
    }
};

import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

import { useAppearance } from '@/composables/useAppearance';
import { watchEffect } from 'vue';

const { resolvedAppearance } = useAppearance();
const isDark = computed(() => resolvedAppearance.value === 'dark');

watchEffect(() => {
    ChartJS.defaults.color = isDark.value ? '#94a3b8' : '#475569';
    ChartJS.defaults.font.family = "'Instrument Sans', sans-serif";
});

const getBarChartData = (data: Record<string, number>, dark: boolean) => {
    return {
        labels: Object.keys(data),
        datasets: [{
            label: 'Active Subscriptions',
            data: Object.values(data),
            backgroundColor: dark ? '#0ea5e9' : '#4f46e5', 
            borderRadius: 4,
            maxBarThickness: 40,
        }]
    };
};

const barChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        x: {
            grid: {
                display: false,
                drawBorder: false,
            },
            ticks: {
                font: { size: 12 },
                maxRotation: 0,
                minRotation: 0,
                callback: function(value: any) {
                    const label = this.getLabelForValue(value);
                    if (typeof label === 'string' && label.length > 15) {
                        return label.substring(0, 15) + '...';
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
            },
            ticks: {
                stepSize: 1,
                padding: 10,
                font: { size: 12 }
            }
        }
    }
}));
import { buttonVariants } from '@/components/ui/button';
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4 md:p-8">
        <!-- Expiring Soon Alert -->
        <div v-if="props.expiringSubscriptions.length > 0" class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-medium">Attention Needed</h3>
                <p class="text-sm mt-1">You have {{ props.expiringSubscriptions.length }} subscription(s) expiring within the next 30 days.</p>
            </div>
            <Link :href="route('subscriptions.index', { status: 'expiring_soon' })" class="text-sm font-medium text-amber-700 underline hover:text-amber-900">
                View All
            </Link>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Dashboard
                </h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Monitor your active subscriptions and revenue metrics.
                </p>
            </div>
            <div class="flex gap-3">
                <Link
                    :href="route('businesses.create')"
                    :class="buttonVariants({ variant: 'secondary' })"
                >
                    Add Business
                </Link>
                <Link
                    :href="route('subscriptions.create')"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    New Subscription
                </Link>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Active Revenue -->
            <div class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-muted-foreground">Active Revenue</span>
                        <span class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ Number(props.totalRevenue).toLocaleString('en-US') }} <span class="text-lg text-muted-foreground">FCFA</span></span>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-orange-500 dark:bg-orange-500/20 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white dark:text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs font-medium text-emerald-600 dark:text-emerald-400">
                    <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    From active subscriptions
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-orange-500 to-orange-400 opacity-80 dark:opacity-40 rounded-b-2xl"></div>
            </div>

            <!-- Total Businesses -->
            <div class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-muted-foreground">Total Businesses</span>
                        <span class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ props.totalBusinesses }}</span>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-orange-500 dark:bg-orange-500/20 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white dark:text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs font-medium text-muted-foreground">
                    Active clients on platform
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-orange-500 to-orange-400 opacity-80 dark:opacity-40 rounded-b-2xl"></div>
            </div>

            <!-- Active Services -->
            <div class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-muted-foreground">Active Services</span>
                        <span class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ props.totalServices }}</span>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-orange-500 dark:bg-orange-500/20 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white dark:text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs font-medium text-muted-foreground">
                    Available for subscription
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-orange-500 to-orange-400 opacity-80 dark:opacity-40 rounded-b-2xl"></div>
            </div>
        </div>


        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Subscriptions by Service Chart -->
            <div
                class="rounded-2xl border border-border bg-card p-6 shadow-sm flex flex-col lg:col-span-2"
            >
                <h2 class="mb-4 text-lg font-semibold text-foreground">
                    Active Subscriptions
                </h2>
                <div class="mt-4 flex-1 h-48 flex items-center justify-center">
                    <Bar v-if="Object.keys(props.subscriptionsByService || {}).length" :data="getBarChartData(props.subscriptionsByService, isDark)" :options="barChartOptions" />
                    <p v-else class="text-sm text-muted-foreground">No data available.</p>
                </div>
            </div>

            <!-- Expiring Soon Widget -->
            <div
                class="flex flex-col rounded-2xl border border-border bg-card p-6 shadow-sm max-h-[300px]"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-foreground">
                        Expiring Soon
                    </h2>
                    <span
                        class="rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-1 text-xs font-bold text-amber-700 dark:text-amber-400"
                        >{{ props.expiringSubscriptions.length }}</span
                    >
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto pr-2">
                    <div
                        v-for="sub in props.expiringSubscriptions"
                        :key="sub.id"
                        class="flex items-center justify-between rounded-lg border border-border bg-muted/50 p-3 hover:bg-muted transition-colors"
                    >
                        <div>
                            <p class="text-sm font-medium text-foreground">
                                {{ sub.business?.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ sub.service?.name }} - {{ new Date(sub.end_date).toLocaleDateString() }}
                            </p>
                        </div>
                        <Link
                            :href="route('subscriptions.renewForm', sub.id)"
                            class="inline-flex items-center rounded-md border border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white transition-colors"
                            >Renew</Link
                        >
                    </div>
                    <div
                        v-if="props.expiringSubscriptions.length === 0"
                        class="py-4 text-center text-sm text-muted-foreground"
                    >
                        All clear! No subscriptions expiring soon.
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
