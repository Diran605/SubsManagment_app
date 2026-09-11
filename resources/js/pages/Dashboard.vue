<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    subscriptions: any;
    expiringSubscriptions: any[];
    totalRevenue: number | string;
    revenueByMode: Record<string, number>;
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

const maxRevenueMode = computed(() => {
    const values = Object.values(props.revenueByMode || {}).map(Number);
    return Math.max(...(values.length ? values : [1]), 1);
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
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4">
        <!-- Expiring Soon Alert -->
        <div v-if="props.expiringSubscriptions.length > 0" class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800 shadow-sm">
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

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Dashboard Overview
                </h1>
                <p class="text-sm text-gray-500">
                    Monitor your active subscriptions and revenue.
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('businesses.create')"
                    class="rounded-lg border border-blue-600 bg-white px-4 py-2 text-sm font-medium text-blue-600 transition-colors hover:bg-blue-50"
                >
                    Add Business
                </Link>
                <Link
                    :href="route('subscriptions.create')"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                >
                    New Subscription
                </Link>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div
                class="flex flex-col rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <span class="text-sm font-medium text-gray-500"
                    >Active Monthly Revenue</span
                >
                <span class="mt-2 text-3xl font-bold text-gray-900"
                    >{{ Number(props.totalRevenue).toLocaleString('en-US') }} FCFA</span
                >
            </div>
            <div
                class="flex flex-col rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <span class="text-sm font-medium text-gray-500"
                    >Total Businesses</span
                >
                <span class="mt-2 text-3xl font-bold text-gray-900">{{
                    props.totalBusinesses
                }}</span>
            </div>
            <div
                class="flex flex-col rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <span class="text-sm font-medium text-gray-500"
                    >Active Services Offered</span
                >
                <span class="mt-2 text-3xl font-bold text-gray-900">{{
                    props.totalServices
                }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Revenue by Mode Chart -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2"
            >
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    Revenue by Mode
                </h2>
                <div class="mt-8 flex h-48 items-end gap-4">
                    <div
                        v-for="(amount, mode) in props.revenueByMode"
                        :key="mode"
                        class="group relative flex h-full flex-1 flex-col items-center justify-end"
                    >
                        <div
                            class="absolute -top-8 rounded bg-gray-900 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            {{ Number(amount).toLocaleString('en-US') }} FCFA
                        </div>
                        <div
                            class="w-full rounded-t-md bg-blue-600 transition-all duration-500"
                            :style="{
                                height: `${Math.max((Number(amount) / maxRevenueMode) * 100, 5)}%`,
                            }"
                        ></div>
                        <span
                            class="mt-2 text-xs font-medium text-gray-500 capitalize"
                            >{{ mode }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Expiring Soon Widget -->
            <div
                class="flex flex-col rounded-xl border border-gray-100 bg-white p-6 shadow-sm max-h-[300px]"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Expiring Soon
                    </h2>
                    <span
                        class="rounded-full bg-amber-100 px-2 py-1 text-xs font-bold text-amber-700"
                        >{{ props.expiringSubscriptions.length }}</span
                    >
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto pr-2">
                    <div
                        v-for="sub in props.expiringSubscriptions"
                        :key="sub.id"
                        class="flex items-center justify-between rounded-lg border border-gray-100 bg-gray-50 p-3"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ sub.business?.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ sub.service?.name }} - {{ new Date(sub.end_date).toLocaleDateString() }}
                            </p>
                        </div>
                        <Link
                            :href="route('subscriptions.renewForm', sub.id)"
                            class="rounded-md border border-blue-200 bg-white px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 hover:text-blue-800"
                            >Renew</Link
                        >
                    </div>
                    <div
                        v-if="props.expiringSubscriptions.length === 0"
                        class="py-4 text-center text-sm text-gray-500"
                    >
                        All clear! No subscriptions expiring soon.
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
