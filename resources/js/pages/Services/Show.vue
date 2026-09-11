<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    service: any;
}>();

const deleteForm = useForm({});

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this service?')) {
        deleteForm.delete(route('services.destroy', props.service.id));
    }
};
</script>

<template>
    <Head :title="service.name" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ service.name }}
                </h1>
                <p class="text-sm text-gray-500">
                    Service Details & Active Subscriptions
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('services.index')"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
                >
                    Back to List
                </Link>
                <Link
                    :href="route('services.edit', service.id)"
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
            <!-- Service Info -->
            <div
                class="col-span-1 h-fit space-y-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <div>
                    <h3 class="mb-2 font-semibold text-gray-900">
                        Description
                    </h3>
                    <p class="text-sm text-gray-600">
                        {{ service.description || 'No description provided.' }}
                    </p>
                </div>

                <div>
                    <h3 class="mb-3 border-b pb-2 font-semibold text-gray-900">
                        Base Pricing
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="price in service.pricing"
                            :key="price.id"
                            class="flex items-center justify-between text-sm"
                        >
                            <span class="text-gray-500 capitalize">{{
                                price.mode
                            }}</span>
                            <span class="font-medium text-gray-900"
                                >{{ price.base_price }} FCFA</span
                            >
                        </div>
                        <div
                            v-if="!service.pricing?.length"
                            class="text-sm text-gray-500"
                        >
                            No pricing set.
                        </div>
                    </div>
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    <dt class="text-xs text-gray-400">Added By</dt>
                    <dd class="mt-1 text-xs text-gray-600">
                        {{ service.creator?.name || 'Unknown' }}
                    </dd>
                </div>
            </div>

            <!-- Subscriptions using this Service -->
            <div
                class="col-span-2 flex flex-col rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-100 p-4"
                >
                    <h3 class="font-semibold text-gray-900">
                        Subscribed Businesses
                    </h3>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b border-gray-100 bg-gray-50 text-gray-600"
                        >
                            <tr>
                                <th class="px-4 py-3 font-semibold">
                                    Business
                                </th>
                                <th class="px-4 py-3 font-semibold">Mode</th>
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
                                v-for="sub in service.subscriptions"
                                :key="sub.id"
                                class="border-b border-gray-50 last:border-0 hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    <Link
                                        :href="route('businesses.show', sub.business_id)"
                                        class="text-blue-600 hover:underline"
                                        >{{ sub.business?.name }}</Link
                                    >
                                </td>
                                <td class="px-4 py-3 text-gray-600 capitalize">
                                    {{ sub.mode }}
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
                                    !service.subscriptions ||
                                    service.subscriptions.length === 0
                                "
                            >
                                <td
                                    colspan="4"
                                    class="py-8 text-center text-gray-500"
                                >
                                    No active subscriptions for this service.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
