<script setup lang="ts">
import { route } from 'ziggy-js';
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    businesses: any;
    filters: { search: string };
}>();

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(
        route('businesses.index'),
        { search: value },
        { preserveState: true, replace: true },
    );
});

import { useForm } from '@inertiajs/vue3';
const deleteForm = useForm({});
const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this business?')) {
        deleteForm.delete(route('businesses.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Businesses" />

    <div class="flex h-full flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Businesses</h1>
                <p class="text-sm text-gray-500">
                    Manage your client businesses.
                </p>
            </div>
            <Link
                :href="route('businesses.create')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Add Business
            </Link>
        </div>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div
                class="flex items-center justify-between border-b border-gray-100 bg-white p-4"
            >
                <input
                    type="text"
                    v-model="search"
                    placeholder="Search businesses..."
                    class="w-full max-w-sm rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                />
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-blue-100 bg-blue-50/50 text-blue-900"
                    >
                        <tr>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold">
                                Contact Person
                            </th>
                            <th class="px-4 py-3 font-semibold">Phone</th>
                            <th class="px-4 py-3 font-semibold">Email</th>
                            <th class="px-4 py-3 text-right font-semibold">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="business in props.businesses.data"
                            :key="business.id"
                            class="border-b border-gray-50 last:border-0 hover:bg-blue-50/30"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ business.name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ business.contact_person || '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ business.phone || '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ business.email || '-' }}
                            </td>
                            <td class="space-x-3 px-4 py-3 text-right">
                                <Link
                                    :href="route('businesses.show', business.id)"
                                    class="font-medium text-blue-600 hover:text-blue-800"
                                    >View</Link
                                >
                                <Link
                                    :href="route('businesses.edit', business.id)"
                                    class="font-medium text-gray-600 hover:text-gray-800"
                                    >Edit</Link
                                >
                                <button
                                    @click="confirmDelete(business.id)"
                                    class="font-medium text-red-600 hover:text-red-800"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="props.businesses.data.length === 0">
                            <td
                                colspan="5"
                                class="py-8 text-center text-gray-500"
                            >
                                No businesses found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="flex items-center justify-between border-t border-gray-100 p-4"
                v-if="props.businesses.links.length > 3"
            >
                <div class="flex gap-1">
                    <Link
                        v-for="(link, k) in props.businesses.links"
                        :key="k"
                        :href="link.url || ''"
                        v-html="link.label"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.active
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-600 hover:bg-gray-100',
                            !link.url ? 'cursor-not-allowed opacity-50' : '',
                        ]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
