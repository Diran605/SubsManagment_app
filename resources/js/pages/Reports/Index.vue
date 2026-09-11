<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    metrics: {
        totalHistoricalRevenue: number;
        totalActiveRevenue: number;
        newRevenue: number;
        renewedRevenue: number;
    };
    charts: {
        revenueByService: Record<string, number>;
        revenueByBusiness: Record<string, number>;
        revenueByMode: Record<string, number>;
    };
}>();

const formatCurrency = (value: number) => {
    return Number(value).toLocaleString('en-US') + ' FCFA';
};
</script>

<template>
    <Head title="Reports & Analytics" />

    <div class="flex h-full flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
            <p class="text-sm text-gray-500">
                Detailed breakdown of your subscription metrics.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Active Revenue</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ formatCurrency(props.metrics.totalActiveRevenue) }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Total Historical Revenue</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ formatCurrency(props.metrics.totalHistoricalRevenue) }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">New Subscriptions Revenue</p>
                <p class="mt-2 text-3xl font-bold text-green-600">{{ formatCurrency(props.metrics.newRevenue) }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Renewals Revenue</p>
                <p class="mt-2 text-3xl font-bold text-blue-600">{{ formatCurrency(props.metrics.renewedRevenue) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm flex flex-col">
                <div class="border-b border-gray-100 p-4">
                    <h3 class="font-semibold text-gray-900">Revenue by Service</h3>
                </div>
                <div class="p-4 flex-1">
                    <ul class="space-y-4">
                        <li v-for="(amount, serviceName) in props.charts.revenueByService" :key="serviceName" class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ serviceName }}</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(amount) }}</span>
                        </li>
                        <li v-if="Object.keys(props.charts.revenueByService).length === 0" class="text-sm text-gray-500">No data</li>
                    </ul>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white shadow-sm flex flex-col">
                <div class="border-b border-gray-100 p-4">
                    <h3 class="font-semibold text-gray-900">Top 10 Businesses by Revenue</h3>
                </div>
                <div class="p-4 flex-1">
                    <ul class="space-y-4">
                        <li v-for="(amount, businessName) in props.charts.revenueByBusiness" :key="businessName" class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 truncate mr-2">{{ businessName }}</span>
                            <span class="font-medium text-gray-900 flex-shrink-0">{{ formatCurrency(amount) }}</span>
                        </li>
                        <li v-if="Object.keys(props.charts.revenueByBusiness).length === 0" class="text-sm text-gray-500">No data</li>
                    </ul>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white shadow-sm flex flex-col">
                <div class="border-b border-gray-100 p-4">
                    <h3 class="font-semibold text-gray-900">Revenue by Mode</h3>
                </div>
                <div class="p-4 flex-1">
                    <ul class="space-y-4">
                        <li v-for="(amount, mode) in props.charts.revenueByMode" :key="mode" class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 capitalize">{{ mode }}</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(amount) }}</span>
                        </li>
                        <li v-if="Object.keys(props.charts.revenueByMode).length === 0" class="text-sm text-gray-500">No data</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
