<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import subscriptionsRoutes from '@/routes/subscriptions';
import businessesRoutes from '@/routes/businesses';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { buttonVariants } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { fieldClass, rowActionVariants, table } from '@/lib/ui';
import { Plus, Search } from '@lucide/vue';

const page = usePage<any>();
const canCreate = computed(() =>
    page.props.auth?.user?.permissions?.includes('create subscriptions'),
);
const canEdit = computed(() =>
    page.props.auth?.user?.permissions?.includes('edit subscriptions'),
);
const canDelete = computed(() =>
    page.props.auth?.user?.permissions?.includes('delete subscriptions'),
);
const canRestore = computed(() =>
    page.props.auth?.user?.permissions?.includes('restore subscriptions'),
);

const props = defineProps<{
    subscriptions: any;
    filters: {
        search?: string;
        status?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

const applyFilters = () => {
    router.get(
        subscriptionsRoutes.index.url(),
        {
            search: search.value,
            status: status.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

// Wait for the user to stop typing before searching, instead of one request per keystroke.
watch(search, useDebounceFn(applyFilters, 300));
watch([status, dateFrom, dateTo], applyFilters);

const deleteForm = useForm({});

const confirmAction = useConfirm();
const confirmDelete = async (id: number) => {
    if (
        await confirmAction({
            title: 'Delete this subscription?',
            description:
                'It stops counting towards revenue. You can restore it later from the Deleted filter.',
            confirmText: 'Delete subscription',
            tone: 'danger',
        })
    ) {
        deleteForm.delete(subscriptionsRoutes.destroy.url(id), {
            preserveScroll: true,
        });
    }
};

const confirmRestore = async (id: number) => {
    if (
        await confirmAction({
            title: 'Restore this subscription?',
            description:
                'It goes back into the subscriptions list and counts towards revenue again.',
            confirmText: 'Restore',
            tone: 'default',
        })
    ) {
        deleteForm.post(subscriptionsRoutes.restore.url(id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Subscriptions" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <PageHeader
            title="Subscriptions"
            description="Every subscription across all businesses."
        >
            <template #actions>
                <Link
                    v-if="canCreate"
                    :href="subscriptionsRoutes.create.url()"
                    :class="buttonVariants()"
                >
                    <Plus class="size-4" /> New subscription
                </Link>
            </template>
        </PageHeader>

        <div :class="table.wrapper">
            <div :class="table.toolbar">
                <div class="relative w-full lg:w-64">
                    <Search
                        class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                    />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search businesses…"
                        aria-label="Search businesses"
                        :class="[fieldClass, 'w-full pl-9']"
                    />
                </div>
                <select
                    v-model="status"
                    aria-label="Filter by status"
                    :class="fieldClass"
                >
                    <option value="all">All statuses</option>
                    <option value="active">Active</option>
                    <option value="expiring_soon">Expiring soon</option>
                    <option value="expired">Expired</option>
                    <option value="renewed">Renewed</option>
                    <option value="trashed">Deleted</option>
                </select>
                <label
                    class="text-muted-foreground flex items-center gap-2 text-sm"
                >
                    From
                    <input v-model="dateFrom" type="date" :class="fieldClass" />
                </label>
                <label
                    class="text-muted-foreground flex items-center gap-2 text-sm"
                >
                    To
                    <input v-model="dateTo" type="date" :class="fieldClass" />
                </label>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead :class="table.head">
                        <tr>
                            <th :class="table.th">Business</th>
                            <th :class="table.th">Service</th>
                            <th :class="table.th">Mode</th>
                            <th :class="[table.th, 'text-right']">Amount</th>
                            <th :class="table.th">Period</th>
                            <th :class="table.th">Added by</th>
                            <th :class="table.th">Status</th>
                            <th :class="[table.th, 'text-right']">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-border divide-y">
                        <tr
                            v-for="sub in props.subscriptions.data"
                            :key="sub.id"
                            :class="table.row"
                        >
                            <td class="text-foreground px-4 py-3 font-medium">
                                <Link
                                    :href="
                                        businessesRoutes.show.url(
                                            sub.business_id,
                                        )
                                    "
                                    class="hover:underline"
                                >
                                    {{ sub.business?.name }}
                                </Link>
                            </td>
                            <td class="text-muted-foreground px-4 py-3">
                                {{ sub.service?.name }}
                            </td>
                            <td
                                class="text-muted-foreground px-4 py-3 capitalize"
                            >
                                {{ sub.mode }}
                            </td>
                            <td
                                class="text-foreground px-4 py-3 text-right font-medium whitespace-nowrap tabular-nums"
                            >
                                {{ Number(sub.amount).toLocaleString('en-US') }}
                                <span class="text-muted-foreground text-xs"
                                    >FCFA</span
                                >
                            </td>
                            <td
                                class="text-muted-foreground px-4 py-3 text-xs whitespace-nowrap"
                            >
                                {{
                                    new Date(
                                        sub.start_date,
                                    ).toLocaleDateString()
                                }}
                                –
                                {{
                                    new Date(sub.end_date).toLocaleDateString()
                                }}
                            </td>
                            <td class="text-muted-foreground px-4 py-3">
                                {{ sub.creator?.name || 'System' }}
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge :status="sub.status_badge" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <Link
                                        :href="
                                            subscriptionsRoutes.show.url(sub.id)
                                        "
                                        :class="
                                            rowActionVariants({
                                                tone: 'neutral',
                                            })
                                        "
                                        >View</Link
                                    >
                                    <template
                                        v-if="sub.status_badge !== 'trashed'"
                                    >
                                        <Link
                                            v-if="
                                                canCreate &&
                                                sub.status_badge !== 'active' &&
                                                sub.status_badge !== 'renewed'
                                            "
                                            :href="
                                                subscriptionsRoutes.createRenewal.url(
                                                    sub.id,
                                                )
                                            "
                                            :class="
                                                rowActionVariants({
                                                    tone: 'success',
                                                })
                                            "
                                            >Renew</Link
                                        >
                                        <Link
                                            v-if="canEdit"
                                            :href="
                                                subscriptionsRoutes.edit.url(
                                                    sub.id,
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
                                            type="button"
                                            @click="confirmDelete(sub.id)"
                                            :class="
                                                rowActionVariants({
                                                    tone: 'danger',
                                                })
                                            "
                                        >
                                            Delete
                                        </button>
                                    </template>
                                    <button
                                        v-else-if="canRestore"
                                        type="button"
                                        @click="confirmRestore(sub.id)"
                                        :class="
                                            rowActionVariants({
                                                tone: 'warning',
                                            })
                                        "
                                    >
                                        Restore
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.subscriptions.data.length === 0">
                            <td colspan="8" :class="table.empty">
                                No subscriptions match these filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="
                    props.subscriptions.links &&
                    props.subscriptions.links.length > 3
                "
                class="border-border flex flex-col items-center justify-between gap-3 border-t px-4 py-3 sm:flex-row"
            >
                <p class="text-muted-foreground text-sm">
                    Showing
                    <span class="text-foreground font-medium">{{
                        props.subscriptions.from
                    }}</span
                    >–<span class="text-foreground font-medium">{{
                        props.subscriptions.to
                    }}</span>
                    of
                    <span class="text-foreground font-medium">{{
                        props.subscriptions.total
                    }}</span>
                </p>
                <nav
                    class="flex flex-wrap items-center gap-1"
                    aria-label="Pagination"
                >
                    <Link
                        v-for="(link, key) in props.subscriptions.links"
                        :key="key"
                        :href="link.url || ''"
                        v-html="link.label"
                        preserve-scroll
                        :aria-current="link.active ? 'page' : undefined"
                        class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2.5 text-sm font-medium transition-colors"
                        :class="[
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                    />
                </nav>
            </div>
        </div>
    </div>
</template>
