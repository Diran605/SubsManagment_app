<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link } from '@inertiajs/vue3';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    staff: any[];
}>();
</script>

<template>
    <Head title="Staff Accounts" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Staff Accounts</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Manage user access and accounts.
                </p>
            </div>
            <Link
                :href="route('staff.create')"
                :class="buttonVariants({ variant: 'default' })"
            >
                Add Staff
            </Link>
        </div>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
        >
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-border bg-orange-500 text-white"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="user in props.staff"
                            :key="user.id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ user.name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ user.email }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-md border px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                        user.is_active
                                            ? 'border-transparent bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 ring-emerald-500/20'
                                            : 'border-transparent bg-destructive/10 text-destructive ring-destructive/20',
                                    ]"
                                >
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link
                                        :href="route('staff.show', user.id)"
                                        class="inline-flex items-center rounded-md border border-primary bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary hover:text-white transition-colors dark:border-primary dark:text-primary"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="route('staff.edit', user.id)"
                                        class="inline-flex items-center rounded-md border border-blue-500 bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-500 hover:text-white transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        :href="route('staff.toggle', user.id)"
                                        method="patch"
                                        as="button"
                                        class="inline-flex items-center rounded-md border border-amber-500 bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-600 dark:text-amber-500 hover:bg-amber-500 hover:text-white transition-colors"
                                    >
                                        {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.staff.length === 0">
                            <td
                                colspan="4"
                                class="py-8 text-center text-muted-foreground"
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
