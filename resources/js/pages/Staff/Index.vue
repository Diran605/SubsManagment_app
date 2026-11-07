<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import PageHeader from '@/components/PageHeader.vue';
import { rowActionVariants, table } from '@/lib/ui';
import { Plus } from '@lucide/vue';
import staffRoutes from '@/routes/staff';
import { Head, Link } from '@inertiajs/vue3';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    staff: any[];
}>();
</script>

<template>
    <Head title="Staff Accounts" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <PageHeader title="Staff Accounts" description="Manage user access and accounts.">
            <template #actions>
                <Link
                    :href="staffRoutes.create.url()"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    <Plus class="size-4" /> Add staff
                </Link>
            </template>
        </PageHeader>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-xl border border-border bg-card shadow-xs"
        >
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        :class="table.head"
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
                            :class="table.row"
                        >
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ user.name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ user.email }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="user.is_active ? 'success' : 'muted'">{{ user.is_active ? 'Active' : 'Inactive' }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link
                                        :href="staffRoutes.show.url(user.id)"
                                        :class="rowActionVariants({ tone: 'neutral' })"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="staffRoutes.edit.url(user.id)"
                                        :class="rowActionVariants({ tone: 'primary' })"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        :href="staffRoutes.toggle.url(user.id)"
                                        method="patch"
                                        as="button"
                                        :class="rowActionVariants({ tone: 'warning' })"
                                    >
                                        {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.staff.length === 0">
                            <td
                                colspan="4"
                                :class="table.empty"
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
