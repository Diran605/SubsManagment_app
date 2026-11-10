<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import { Badge } from '@/components/ui/badge';
import PageHeader from '@/components/PageHeader.vue';
import { rowActionVariants, table } from '@/lib/ui';
import { Plus } from '@lucide/vue';
import servicesRoutes from '@/routes/services';
import { Head, Link, usePage } from '@inertiajs/vue3';
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
                            <th class="px-4 py-3 font-medium">Service Name</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">Configuration</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-border divide-y">
                        <tr
                            v-for="service in props.services"
                            :key="service.id"
                            :class="table.row"
                        >
                            <td class="text-foreground px-4 py-3 font-medium">
                                <div class="flex items-center gap-2">
                                    <span>{{ service.name }}</span>
                                    <Badge
                                        v-if="service.category === 'education'"
                                        variant="info"
                                        >Education</Badge
                                    >
                                </div>
                            </td>
                            <td
                                class="text-muted-foreground max-w-xs truncate px-4 py-3"
                            >
                                {{ service.description || '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Badge variant="muted"
                                        >{{
                                            service.packages?.length || 0
                                        }}
                                        packages</Badge
                                    >
                                    <Badge variant="muted"
                                        >{{
                                            service.modules?.length || 0
                                        }}
                                        modules</Badge
                                    >
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <Link
                                        :href="
                                            servicesRoutes.show.url(service.id)
                                        "
                                        :class="
                                            rowActionVariants({
                                                tone: 'neutral',
                                            })
                                        "
                                        >View</Link
                                    >
                                    <Link
                                        v-if="canEdit"
                                        :href="
                                            servicesRoutes.edit.url(service.id)
                                        "
                                        :class="
                                            rowActionVariants({
                                                tone: 'primary',
                                            })
                                        "
                                        >Edit</Link
                                    >
                                    <button
                                        v-if="canDelete"
                                        @click="confirmDelete(service.id)"
                                        :class="
                                            rowActionVariants({
                                                tone: 'danger',
                                            })
                                        "
                                    >
                                        Delete
                                    </button>
                                </div>
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
