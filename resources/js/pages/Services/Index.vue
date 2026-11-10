<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import { Badge } from '@/components/ui/badge';
import PageHeader from '@/components/PageHeader.vue';
import EntityAvatar from '@/components/EntityAvatar.vue';
import RowActions from '@/components/RowActions.vue';
import { table } from '@/lib/ui';
import { Eye, Package, Pencil, Plus, Puzzle, Trash2 } from '@lucide/vue';
import servicesRoutes from '@/routes/services';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canCreate = computed(() =>
    page.props.auth?.user?.permissions?.includes('create services'),
);
const canEdit = computed(() =>
    page.props.auth?.user?.permissions?.includes('edit services'),
);
const canDelete = computed(() =>
    page.props.auth?.user?.permissions?.includes('delete services'),
);

const props = defineProps<{
    services: any[];
}>();

import { useForm } from '@inertiajs/vue3';
const deleteForm = useForm({});
const confirmAction = useConfirm();
const confirmDelete = async (id: number) => {
    if (
        await confirmAction({
            title: 'Delete this service?',
            description:
                'It disappears from lists and can no longer be picked for new subscriptions.',
            confirmText: 'Delete service',
            tone: 'danger',
        })
    ) {
        deleteForm.delete(servicesRoutes.destroy.url(id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Services" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <PageHeader
            title="Services"
            description="Manage the services you offer."
        >
            <template #actions>
                <Link
                    v-if="canCreate"
                    :href="servicesRoutes.create.url()"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    <Plus class="size-4" /> Add service
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
                            <th :class="table.th">Service</th>
                            <th :class="[table.th, 'hidden md:table-cell']">
                                Description
                            </th>
                            <th :class="table.th">Catalogue</th>
                            <th :class="[table.th, 'w-12']">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-border divide-y">
                        <tr
                            v-for="service in props.services"
                            :key="service.id"
                            :class="table.clickableRow"
                            @click="
                                router.visit(
                                    servicesRoutes.show.url(service.id),
                                )
                            "
                        >
                            <td :class="table.cell">
                                <div class="flex items-center gap-3">
                                    <EntityAvatar :name="service.name" />
                                    <div class="min-w-0">
                                        <div
                                            class="text-foreground truncate font-medium"
                                        >
                                            {{ service.name }}
                                        </div>
                                        <div
                                            class="text-muted-foreground text-xs"
                                        >
                                            {{
                                                service.category === 'education'
                                                    ? 'Education · per student'
                                                    : 'Standard · monthly'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                :class="[
                                    table.cell,
                                    'text-muted-foreground hidden max-w-sm md:table-cell',
                                ]"
                            >
                                <span class="line-clamp-2 text-xs">{{
                                    service.description || '—'
                                }}</span>
                            </td>
                            <td :class="table.cell">
                                <div
                                    class="text-muted-foreground flex flex-wrap gap-x-3 gap-y-1 text-xs"
                                >
                                    <span class="inline-flex items-center gap-1"
                                        ><Package class="size-3.5" />
                                        <span
                                            class="text-foreground font-medium"
                                            >{{
                                                service.packages?.length || 0
                                            }}</span
                                        >
                                        packages</span
                                    >
                                    <span class="inline-flex items-center gap-1"
                                        ><Puzzle class="size-3.5" />
                                        <span
                                            class="text-foreground font-medium"
                                            >{{
                                                service.modules?.length || 0
                                            }}</span
                                        >
                                        modules</span
                                    >
                                </div>
                            </td>
                            <td :class="[table.cell, 'text-right']">
                                <RowActions
                                    :label="`Actions for ${service.name}`"
                                    :actions="[
                                        {
                                            label: 'View',
                                            icon: Eye,
                                            href: servicesRoutes.show.url(
                                                service.id,
                                            ),
                                        },
                                        {
                                            label: 'Edit',
                                            icon: Pencil,
                                            href: servicesRoutes.edit.url(
                                                service.id,
                                            ),
                                            hidden: !canEdit,
                                        },
                                        {
                                            label: 'Delete',
                                            icon: Trash2,
                                            danger: true,
                                            onSelect: () =>
                                                confirmDelete(service.id),
                                            hidden: !canDelete,
                                        },
                                    ]"
                                />
                            </td>
                        </tr>
                        <tr v-if="props.services.length === 0">
                            <td colspan="4" :class="table.empty">
                                No services found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
