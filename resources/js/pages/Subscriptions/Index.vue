<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
    subscriptions: any;
    filters: {
        search?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

watch([search, status, dateFrom, dateTo], ([newSearch, newStatus, newFrom, newTo]) => {
    router.get(
        route('subscriptions.index'),
        { search: newSearch, status: newStatus, date_from: newFrom, date_to: newTo },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

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
    <Head title="Subscriptions" />

    <div class="flex h-full flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Subscriptions</h1>
                <p class="text-sm text-gray-500">
                    Master list of all subscriptions.
                </p>
            </div>
            <Link
                :href="route('subscriptions.create')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                New Subscription
            </Link>
        </div>

        <div class="flex flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between border-b border-gray-100 bg-gray-50/50 p-4 gap-4">
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search businesses..."
                        class="w-full lg:w-64 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <select
                        v-model="status"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="expiring_soon">Expiring Soon</option>
                        <option value="expired">Expired</option>
                    </select>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">From:</span>
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">To:</span>
                        <input
                            v-model="dateTo"
                            type="date"
                            class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Business</th>
                            <th class="px-4 py-3 font-medium">Service</th>
                            <th class="px-4 py-3 font-medium">Mode</th>
                            <th class="px-4 py-3 font-medium">Amount</th>
                            <th class="px-4 py-3 font-medium">Period</th>
                            <th class="px-4 py-3 font-medium">Added By</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="sub in props.subscriptions.data"
                            :key="sub.id"
                            class="transition-colors hover:bg-blue-50/30"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <Link :href="route('businesses.show', sub.business_id)" class="hover:underline">
                                    {{ sub.business?.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ sub.service?.name }}</td>
                            <td class="px-4 py-3 text-gray-600 capitalize">{{ sub.mode }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ Number(sub.amount).toLocaleString('en-US') }} FCFA</td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ new Date(sub.start_date).toLocaleDateString() }} <br />to {{ new Date(sub.end_date).toLocaleDateString() }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ sub.creator?.name || 'System' }}</td>
                            <td class="px-4 py-3">
                                <span
                                    :class="{
                                        'rounded-full px-2 py-1 text-xs font-semibold': true,
                                        'bg-green-100 text-green-700': sub.status_badge === 'active',
                                        'bg-amber-100 text-amber-700': sub.status_badge === 'expiring_soon',
                                        'bg-red-100 text-red-700': sub.status_badge === 'expired',
                                        'bg-gray-100 text-gray-700': sub.status_badge === 'renewed',
                                    }"
                                >
                                    {{ sub.status_badge.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="space-x-2 px-4 py-3 text-right">
                                <Link
                                    v-if="sub.status_badge !== 'active' && sub.status_badge !== 'renewed'"
                                    :href="route('subscriptions.renewForm', sub.id)"
                                    class="text-xs font-medium text-green-600 hover:text-green-800"
                                >Renew</Link>
                                <Link
                                    :href="route('subscriptions.edit', sub.id)"
                                    class="text-xs font-medium text-blue-600 hover:text-blue-800"
                                >Edit</Link>
                                <button
                                    @click="confirmDelete(sub.id)"
                                    class="text-xs font-medium text-red-600 hover:text-red-800"
                                >Delete</button>
                            </td>
                        </tr>
                        <tr v-if="props.subscriptions.data.length === 0">
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                No subscriptions found matching filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.subscriptions.links && props.subscriptions.links.length > 3"
                class="flex items-center justify-between border-t border-gray-100 bg-white px-4 py-3 sm:px-6"
            >
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ props.subscriptions.from }}</span>
                            to
                            <span class="font-medium">{{ props.subscriptions.to }}</span>
                            of
                            <span class="font-medium">{{ props.subscriptions.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <Link
                                v-for="(link, key) in props.subscriptions.links"
                                :key="key"
                                :href="link.url || ''"
                                v-html="link.label"
                                preserve-scroll
                                class="relative inline-flex items-center border px-4 py-2 text-sm font-medium focus:z-20"
                                :class="[
                                    link.active ? 'z-10 border-blue-500 bg-blue-50 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50',
                                    !link.url ? 'pointer-events-none text-gray-300' : '',
                                ]"
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
