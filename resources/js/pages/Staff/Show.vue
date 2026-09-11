<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    staff: any;
}>();
</script>

<template>
    <Head :title="staff.name" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ staff.name }}
                </h1>
                <p class="text-sm text-gray-500">
                    Staff Details & Audit Log
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('staff.index')"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
                >
                    Back to List
                </Link>
                <Link
                    :href="route('staff.edit', staff.id)"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                >
                    Edit
                </Link>
                <Link
                    :href="route('staff.toggle', staff.id)"
                    method="patch"
                    as="button"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
                    :class="staff.is_active ? 'border-amber-200 bg-white text-amber-600 hover:bg-amber-50' : 'border-green-200 bg-white text-green-600 hover:bg-green-50'"
                >
                    {{ staff.is_active ? 'Deactivate' : 'Activate' }}
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div
                class="col-span-1 h-fit space-y-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ staff.email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span
                                :class="[
                                    'rounded-full border px-2 py-1 text-xs font-semibold',
                                    staff.is_active
                                        ? 'border-green-200 bg-green-100 text-green-700'
                                        : 'border-red-200 bg-red-100 text-red-700',
                                ]"
                            >
                                {{ staff.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Joined</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ new Date(staff.created_at).toLocaleDateString() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="col-span-2 space-y-6">
                <!-- Businesses Created -->
                <div class="flex flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-4">
                        <h3 class="font-semibold text-gray-900">Businesses Added</h3>
                    </div>
                    <div class="p-4" v-if="staff.businesses?.length === 0">
                        <p class="text-sm text-gray-500">No businesses added by this staff member.</p>
                    </div>
                    <ul class="divide-y divide-gray-100" v-else>
                        <li v-for="b in staff.businesses" :key="b.id" class="p-4">
                            <Link :href="route('businesses.show', b.id)" class="text-sm font-medium text-blue-600 hover:underline">{{ b.name }}</Link>
                            <span class="ml-2 text-xs text-gray-500">on {{ new Date(b.created_at).toLocaleDateString() }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Subscriptions Created -->
                <div class="flex flex-col rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-4">
                        <h3 class="font-semibold text-gray-900">Subscriptions Handled</h3>
                    </div>
                    <div class="p-4" v-if="staff.subscriptions?.length === 0">
                        <p class="text-sm text-gray-500">No subscriptions handled by this staff member.</p>
                    </div>
                    <ul class="divide-y divide-gray-100" v-else>
                        <li v-for="sub in staff.subscriptions" :key="sub.id" class="p-4 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ sub.mode }}</span>
                                <span class="ml-2 text-xs text-gray-500">from {{ sub.start_date }} to {{ sub.end_date }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ Number(sub.amount).toLocaleString('en-US') }} FCFA</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
