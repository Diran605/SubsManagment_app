<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    business: any;
}>();

const deleteForm = useForm({});

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this business?')) {
        deleteForm.delete(route('businesses.destroy', props.business.id));
    }
};
</script>

<template>
    <Head :title="business.name" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ business.name }}
                </h1>
                <p class="text-sm text-gray-500">
                    Business Details & Subscription History
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('businesses.index')"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
                >
                    Back to List
                </Link>
                <Link
                    :href="route('businesses.edit', business.id)"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                >
                    Edit
                </Link>
                <button
                    @click="confirmDelete"
                    class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
                >
                    Delete
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Business Info -->
            <div
                class="col-span-1 h-fit rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <h3 class="mb-4 border-b pb-2 font-semibold text-gray-900">
                    Contact Information
                </h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">
                            Contact Person
                        </dt>
                        <dd class="mt-1 text-gray-900">
                            {{ business.contact_person || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-gray-900">
                            {{ business.email || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-gray-900">
                            {{ business.phone || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-gray-900">
                            {{ business.address || 'N/A' }}
                        </dd>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <dt class="text-xs text-gray-400">Added By</dt>
                        <dd class="mt-1 text-xs text-gray-600">
                            {{ business.creator?.name || 'Unknown' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Subscriptions History -->
            <div
                class="col-span-2 flex flex-col rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-100 p-4"
                >
                    <h3 class="font-semibold text-gray-900">Subscriptions</h3>
                    <Link
                        :href="route('subscriptions.create', { business_id: business.id })"
                        class="text-sm font-medium text-blue-600 hover:text-blue-800"
                    >
                        + New Subscription
                    </Link>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b border-gray-100 bg-gray-50 text-gray-600"
                        >
                            <tr>
                                <th class="px-4 py-3 font-semibold">Service</th>
                                <th class="px-4 py-3 font-semibold">Mode</th>
                                <th class="px-4 py-3 font-semibold">
                                    Start Date
                                </th>
                                <th class="px-4 py-3 font-semibold">
                                    End Date
                                </th>
                                <th class="px-4 py-3 text-right font-semibold">
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="sub in business.subscriptions"
                                :key="sub.id"
                                class="border-b border-gray-50 last:border-0 hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ sub.service?.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 capitalize">
                                    {{ sub.mode }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ sub.start_date }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ sub.end_date }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium text-gray-900"
                                >
                                    {{ Number(sub.amount).toLocaleString('en-US') }} FCFA
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !business.subscriptions ||
                                    business.subscriptions.length === 0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="py-8 text-center text-gray-500"
                                >
                                    No subscriptions yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
