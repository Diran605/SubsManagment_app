<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import PageHeader from '@/components/PageHeader.vue';
import EntityAvatar from '@/components/EntityAvatar.vue';
import RowActions from '@/components/RowActions.vue';
import { table } from '@/lib/ui';
import { Eye, Pencil, Plus, Power } from '@lucide/vue';
import staffRoutes from '@/routes/staff';
import { Head, Link, router } from '@inertiajs/vue3';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    staff: any[];
}>();
</script>

<template>
    <Head title="Staff Accounts" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <PageHeader
            title="Staff Accounts"
            description="Manage user access and accounts."
        >
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
            class="border-border bg-card flex flex-1 flex-col overflow-hidden rounded-xl border shadow-xs"
        >
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead :class="table.head">
                        <tr>
                            <th :class="table.th">Name</th>
                            <th :class="[table.th, 'hidden md:table-cell']">
                                Roles
                            </th>
                            <th :class="table.th">Status</th>
                            <th :class="[table.th, 'w-12']">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-border divide-y">
                        <tr
                            v-for="user in props.staff"
                            :key="user.id"
                            :class="table.clickableRow"
                            @click="router.visit(staffRoutes.show.url(user.id))"
                        >
                            <td :class="table.cell">
                                <div class="flex items-center gap-3">
                                    <EntityAvatar :name="user.name" />
                                    <div class="min-w-0">
                                        <div
                                            class="text-foreground truncate font-medium"
                                        >
                                            {{ user.name }}
                                        </div>
                                        <div
                                            class="text-muted-foreground truncate text-xs"
                                        >
                                            {{ user.email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td :class="[table.cell, 'hidden md:table-cell']">
                                <div
                                    v-if="user.roles?.length"
                                    class="flex flex-wrap gap-1"
                                >
                                    <Badge
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        variant="muted"
                                        >{{ role.name }}</Badge
                                    >
                                </div>
                                <span v-else class="text-muted-foreground"
                                    >—</span
                                >
                            </td>
                            <td :class="table.cell">
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-medium"
                                    :class="
                                        user.is_active
                                            ? 'text-success'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    <span
                                        class="size-1.5 rounded-full"
                                        :class="
                                            user.is_active
                                                ? 'bg-success'
                                                : 'bg-muted-foreground/50'
                                        "
                                    />
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td :class="[table.cell, 'text-right']">
                                <RowActions
                                    :label="`Actions for ${user.name}`"
                                    :actions="[
                                        {
                                            label: 'View',
                                            icon: Eye,
                                            href: staffRoutes.show.url(user.id),
                                        },
                                        {
                                            label: 'Edit',
                                            icon: Pencil,
                                            href: staffRoutes.edit.url(user.id),
                                        },
                                        {
                                            label: user.is_active
                                                ? 'Deactivate'
                                                : 'Activate',
                                            icon: Power,
                                            href: staffRoutes.toggle.url(
                                                user.id,
                                            ),
                                            method: 'patch',
                                        },
                                    ]"
                                />
                            </td>
                        </tr>
                        <tr v-if="props.staff.length === 0">
                            <td colspan="4" :class="table.empty">
                                No staff found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
