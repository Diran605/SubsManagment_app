<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import Pagination from '@/components/Pagination.vue';
import PageHeader from '@/components/PageHeader.vue';
import { fieldClass, rowActionVariants, table } from '@/lib/ui';
import { Plus, Search } from '@lucide/vue';
import businessesRoutes from '@/routes/businesses';
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canCreate = computed(() =>
    page.props.auth?.user?.permissions?.includes('create businesses'),
);
const canEdit = computed(() =>
    page.props.auth?.user?.permissions?.includes('edit businesses'),
);
const canDelete = computed(() =>
    page.props.auth?.user?.permissions?.includes('delete businesses'),
);

const props = defineProps<{
    businesses: any;
    filters: { search: string };
}>();

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(
        businessesRoutes.index.url(),
        { search: value },
        { preserveState: true, replace: true },
    );
});

import { useForm } from '@inertiajs/vue3';
const deleteForm = useForm({});
const confirmAction = useConfirm();
const confirmDelete = async (id: number) => {
    if (
        await confirmAction({
            title: 'Delete this business?',
            description:
                'It disappears from lists, and its subscriptions stop counting towards revenue.',
            confirmText: 'Delete business',
            tone: 'danger',
        })
    ) {
        deleteForm.delete(businessesRoutes.destroy.url(id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Businesses" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <PageHeader
            title="Businesses"
            description="Manage your client businesses."
        >
            <template #actions>
                <Link
                    v-if="canCreate"
                    :href="businessesRoutes.create.url()"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    <Plus class="size-4" /> Add business
                </Link>
            </template>
        </PageHeader>

        <div
            class="border-border bg-card flex flex-1 flex-col overflow-hidden rounded-xl border shadow-xs"
        >
            <div :class="table.toolbar">
                <div class="relative w-full max-w-sm">
                    <Search
                        class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                    />
                    <input
                        v-model="search"
                        type="search"
                        aria-label="Search businesses"
                        placeholder="Search businesses…"
                        :class="[fieldClass, 'w-full pl-9']"
                    />
                </div>
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead :class="table.head">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">
                                Contact Person
                            </th>
                            <th class="px-4 py-3 font-medium">Phone</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-border divide-y">
                        <tr
                            v-for="business in props.businesses.data"
                            :key="business.id"
                            :class="table.row"
                        >
                            <td class="text-foreground px-4 py-3 font-medium">
                                {{ business.name }}
                            </td>
                            <td class="text-muted-foreground px-4 py-3">
                                {{ business.contact_person || '—' }}
                            </td>
                            <td class="text-muted-foreground px-4 py-3">
                                {{ business.phone || '—' }}
                            </td>
                            <td class="text-muted-foreground px-4 py-3">
                                {{ business.email || '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <Link
                                        :href="
                                            businessesRoutes.show.url(
                                                business.id,
                                            )
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
                                            businessesRoutes.edit.url(
                                                business.id,
                                            )
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
                                        @click="confirmDelete(business.id)"
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
                        <tr v-if="props.businesses.data.length === 0">
                            <td colspan="5" :class="table.empty">
                                No businesses found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                :links="props.businesses.links"
                :from="props.businesses.from"
                :to="props.businesses.to"
                :total="props.businesses.total"
            />
        </div>
    </div>
</template>
