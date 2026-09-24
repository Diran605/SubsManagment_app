<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    logs: any[];
}>();
</script>

<template>
    <Head title="Audit Logs" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-foreground">Audit Logs</h1>
            <p class="text-sm text-muted-foreground mt-1">
                Timeline of actions performed by staff members.
            </p>
        </div>

        <div class="flex-1 overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-orange-500 text-white">
                        <tr>
                            <th class="px-4 py-3 font-medium">Date & Time</th>
                            <th class="px-4 py-3 font-medium">User (Staff)</th>
                            <th class="px-4 py-3 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="log in props.logs" :key="log.id" class="transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                {{ new Date(log.created_at).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ log.user }}
                            </td>
                            <td class="px-4 py-3 text-foreground">
                                <span v-if="log.type === 'business'" class="mr-2 inline-block rounded-md bg-blue-500/10 px-2 py-0.5 text-xs text-blue-600 dark:text-blue-400 ring-1 ring-inset ring-blue-500/20">Business</span>
                                <span v-else-if="log.type === 'service'" class="mr-2 inline-block rounded-md bg-purple-500/10 px-2 py-0.5 text-xs text-purple-600 dark:text-purple-400 ring-1 ring-inset ring-purple-500/20">Service</span>
                                <span v-else class="mr-2 inline-block rounded-md bg-emerald-500/10 px-2 py-0.5 text-xs text-emerald-600 dark:text-emerald-400 ring-1 ring-inset ring-emerald-500/20">Subscription</span>
                                {{ log.action }}
                            </td>
                        </tr>
                        <tr v-if="props.logs.length === 0">
                            <td colspan="3" class="py-8 text-center text-muted-foreground">
                                No activity found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
