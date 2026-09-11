<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    services: any[];
}>();

import { useForm } from '@inertiajs/vue3';
const deleteForm = useForm({});
const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this service?')) {
        deleteForm.delete(route('services.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Services" />

    <div class="flex h-full flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Services</h1>
                <p class="text-sm text-gray-500">
                    Manage the services you offer.
                </p>
            </div>
            <Link
                :href="route('services.create')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Add Service
            </Link>
        </div>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-blue-100 bg-blue-50/50 text-blue-900"
                    >
                        <tr>
                            <th class="px-4 py-3 font-semibold">
                                Service Name
                            </th>
                            <th class="px-4 py-3 font-semibold">Description</th>
                            <th class="px-4 py-3 font-semibold">
                                Pricing Modes
                            </th>
                            <th class="px-4 py-3 text-right font-semibold">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="service in props.services"
                            :key="service.id"
                            class="border-b border-gray-50 last:border-0 hover:bg-blue-50/30"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ service.name }}
                            </td>
                            <td
                                class="max-w-xs truncate px-4 py-3 text-gray-600"
                            >
                                {{ service.description || '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="pricing in service.pricing"
                                        :key="pricing.id"
                                        class="rounded border border-gray-200 bg-gray-100 px-2 py-1 text-xs text-gray-700"
                                    >
                                        {{ pricing.mode }}:
                                        {{ pricing.base_price }}
                                    </span>
                                    <span
                                        v-if="!service.pricing?.length"
                                        class="text-xs text-gray-400"
                                        >No pricing set</span
                                    >
                                </div>
                            </td>
                            <td class="space-x-3 px-4 py-3 text-right">
                                <Link
                                    :href="route('services.show', service.id)"
                                    class="font-medium text-blue-600 hover:text-blue-800"
                                    >View</Link
                                >
                                <Link
                                    :href="route('services.edit', service.id)"
                                    class="font-medium text-gray-600 hover:text-gray-800"
                                    >Edit</Link
                                >
                                <button
                                    @click="confirmDelete(service.id)"
                                    class="font-medium text-red-600 hover:text-red-800"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="props.services.length === 0">
                            <td
                                colspan="4"
                                class="py-8 text-center text-gray-500"
                            >
                                No services found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
