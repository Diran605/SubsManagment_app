<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import Pagination from '@/components/Pagination.vue';
import PageHeader from '@/components/PageHeader.vue';
import EntityAvatar from '@/components/EntityAvatar.vue';
import RowActions from '@/components/RowActions.vue';
import { fieldClass, table } from '@/lib/ui';
import {
    Eye,
    Mail,
    MapPin,
    Pencil,
    Phone,
    Plus,
    Search,
    Trash2,
} from '@lucide/vue';
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
                            <th :class="table.th">Business</th>
                            <th :class="table.th">Contact</th>
                            <th :class="[table.th, 'hidden lg:table-cell']">
                                Location
                            </th>
                            <th :class="[table.th, 'w-12']">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-border divide-y">
                        <tr
                            v-for="business in props.businesses.data"
                            :key="business.id"
                            :class="table.clickableRow"
                            @click="
                                router.visit(
                                    businessesRoutes.show.url(business.id),
                                )
                            "
                        >
                            <td :class="table.cell">
                                <div class="flex items-center gap-3">
                                    <EntityAvatar :name="business.name" />
                                    <div class="min-w-0">
                                        <div
                                            class="text-foreground truncate font-medium"
                                        >
                                            {{ business.name }}
                                        </div>
                                        <div
                                            class="text-muted-foreground truncate text-xs"
                                        >
                                            {{
                                                business.contact_person ||
                                                'No contact person'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td :class="table.cell">
                                <div
                                    v-if="business.phone || business.email"
                                    class="space-y-0.5 text-xs"
                                >
                                    <div
                                        v-if="business.phone"
                                        class="text-foreground flex items-center gap-1.5"
                                    >
                                        <Phone
                                            class="text-muted-foreground size-3.5"
                                        />
                                        {{ business.phone }}
                                    </div>
                                    <div
                                        v-if="business.email"
                                        class="text-muted-foreground flex items-center gap-1.5"
                                    >
                                        <Mail class="size-3.5" />
                                        <span class="truncate">{{
                                            business.email
                                        }}</span>
                                    </div>
                                </div>
                                <span v-else class="text-muted-foreground"
                                    >—</span
                                >
                            </td>
                            <td
                                :class="[
                                    table.cell,
                                    'text-muted-foreground hidden max-w-xs text-xs lg:table-cell',
                                ]"
                            >
                                <div
                                    v-if="business.landmark || business.address"
                                    class="flex items-start gap-1.5"
                                >
                                    <MapPin class="mt-0.5 size-3.5 shrink-0" />
                                    <span class="line-clamp-2">{{
                                        business.landmark || business.address
                                    }}</span>
                                </div>
                                <span v-else>—</span>
                            </td>
                            <td :class="[table.cell, 'text-right']">
                                <RowActions
                                    :label="`Actions for ${business.name}`"
                                    :actions="[
                                        {
                                            label: 'View',
                                            icon: Eye,
                                            href: businessesRoutes.show.url(
                                                business.id,
                                            ),
                                        },
                                        {
                                            label: 'Edit',
                                            icon: Pencil,
                                            href: businessesRoutes.edit.url(
                                                business.id,
                                            ),
                                            hidden: !canEdit,
                                        },
                                        {
                                            label: 'Delete',
                                            icon: Trash2,
                                            danger: true,
                                            onSelect: () =>
                                                confirmDelete(business.id),
                                            hidden: !canDelete,
                                        },
                                    ]"
                                />
                            </td>
                        </tr>
                        <tr v-if="props.businesses.data.length === 0">
                            <td colspan="4" :class="table.empty">
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
