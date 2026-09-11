<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    logs: any[];
}>();
</script>

<template>
    <Head title="Audit Logs" />

    <div class="flex h-full flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
            <p class="text-sm text-gray-500">
                Timeline of actions performed by staff members.
            </p>
        </div>

        <div class="flex-1 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Date & Time</th>
                            <th class="px-4 py-3 font-medium">User (Staff)</th>
                            <th class="px-4 py-3 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="log in props.logs" :key="log.id" class="transition-colors hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                {{ new Date(log.created_at).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ log.user }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                <span v-if="log.type === 'business'" class="mr-2 inline-block rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700">Business</span>
                                <span v-else-if="log.type === 'service'" class="mr-2 inline-block rounded bg-purple-100 px-2 py-0.5 text-xs text-purple-700">Service</span>
                                <span v-else class="mr-2 inline-block rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Subscription</span>
                                {{ log.action }}
                            </td>
                        </tr>
                        <tr v-if="props.logs.length === 0">
                            <td colspan="3" class="py-8 text-center text-gray-500">
                                No activity found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
