<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    staff: any[];
}>();
</script>

<template>
    <Head title="Staff Accounts" />

    <div class="flex h-full flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Staff Accounts</h1>
                <p class="text-sm text-gray-500">
                    Manage user access and accounts.
                </p>
            </div>
            <Link
                :href="route('staff.create')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Add Staff
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
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold">Email</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in props.staff"
                            :key="user.id"
                            class="border-b border-gray-50 last:border-0 hover:bg-blue-50/30"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ user.name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ user.email }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-full border px-2 py-1 text-xs font-semibold',
                                        user.is_active
                                            ? 'border-green-200 bg-green-100 text-green-700'
                                            : 'border-red-200 bg-red-100 text-red-700',
                                    ]"
                                >
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="space-x-3 px-4 py-3 text-right">
                                <Link
                                    :href="route('staff.show', user.id)"
                                    class="font-medium text-blue-600 hover:text-blue-800"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="route('staff.edit', user.id)"
                                    class="font-medium text-gray-600 hover:text-gray-800"
                                >
                                    Edit
                                </Link>
                                <Link
                                    :href="route('staff.toggle', user.id)"
                                    method="patch"
                                    as="button"
                                    class="font-medium text-amber-600 hover:text-amber-800"
                                >
                                    {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="props.staff.length === 0">
                            <td
                                colspan="4"
                                class="py-8 text-center text-gray-500"
                            >
                                No staff found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
