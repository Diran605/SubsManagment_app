<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canCreate = computed(() => page.props.auth?.user?.permissions?.includes('create subscriptions'));
const canEdit = computed(() => page.props.auth?.user?.permissions?.includes('edit subscriptions'));
const canDelete = computed(() => page.props.auth?.user?.permissions?.includes('delete subscriptions'));
const canRestore = computed(() => page.props.auth?.user?.permissions?.includes('restore subscriptions'));

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

watch([search, status, dateFrom, dateTo], ([newSearch, newStatus, newFrom, newTo]) => {
    router.get(
        route('subscriptions.index'),
        { search: newSearch, status: newStatus, date_from: newFrom, date_to: newTo },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const deleteForm = useForm({});

const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this subscription?')) {
        deleteForm.delete(route('subscriptions.destroy', id), {
            preserveScroll: true
        });
    }
};

const confirmRestore = (id: number) => {
    if (confirm('Are you sure you want to restore this subscription?')) {
        deleteForm.post(route('subscriptions.restore', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Subscriptions" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Subscriptions</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Master list of all subscriptions.
                </p>
            </div>
            <Link
                v-if="canCreate"
                :href="route('subscriptions.create')"
                :class="buttonVariants({ variant: 'default' })"
            >
                New Subscription
            </Link>
        </div>

        <div class="flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
            <div class="flex flex-wrap items-center justify-between border-b border-border bg-muted/30 p-4 gap-4">
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search businesses..."
                        class="w-full lg:w-64 rounded-md border border-input bg-background px-3 py-1.5 text-sm text-foreground focus:border-primary focus:ring-primary"
                    />
                    <select
                        v-model="status"
                        class="rounded-md border border-input bg-background px-3 py-1.5 text-sm text-foreground focus:border-primary focus:ring-primary"
                    >
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="expiring_soon">Expiring Soon</option>
                        <option value="expired">Expired</option>
                        <option value="trashed">Deleted (Trashed)</option>
                    </select>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground">From:</span>
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="rounded-md border border-input bg-background px-3 py-1.5 text-sm text-foreground focus:border-primary focus:ring-primary"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground">To:</span>
                        <input
                            v-model="dateTo"
                            type="date"
                            class="rounded-md border border-input bg-background px-3 py-1.5 text-sm text-foreground focus:border-primary focus:ring-primary"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-orange-500 text-white">
                        <tr>
                            <th class="px-4 py-3 font-medium">Business</th>
                            <th class="px-4 py-3 font-medium">Service</th>
                            <th class="px-4 py-3 font-medium">Mode</th>
                            <th class="px-4 py-3 font-medium">Amount</th>
                            <th class="px-4 py-3 font-medium">Period</th>
                            <th class="px-4 py-3 font-medium">Added By</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="sub in props.subscriptions.data"
                            :key="sub.id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 font-medium text-foreground">
                                <Link :href="route('businesses.show', sub.business_id)" class="hover:underline">
                                    {{ sub.business?.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ sub.service?.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground capitalize">{{ sub.mode }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">{{ Number(sub.amount).toLocaleString('en-US') }} FCFA</td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                {{ new Date(sub.start_date).toLocaleDateString() }} <br />to {{ new Date(sub.end_date).toLocaleDateString() }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ sub.creator?.name || 'System' }}</td>
                            <td class="px-4 py-3">
                                <span
                                    :class="{
                                        'rounded-full px-2 py-1 text-xs font-semibold': true,
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': sub.status_badge === 'active',
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': sub.status_badge === 'expiring_soon',
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': sub.status_badge === 'expired',
                                        'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300': sub.status_badge === 'renewed',
                                        'bg-slate-800 text-white dark:bg-slate-700': sub.status_badge === 'trashed',
                                    }"
                                >
                                    {{ sub.status_badge.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link
                                        :href="route('subscriptions.show', sub.id)"
                                        class="inline-flex items-center rounded-md border border-primary bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary hover:text-white transition-colors dark:border-primary dark:text-primary"
                                    >View</Link>
                                    <template v-if="sub.status_badge !== 'trashed'">
                                        <Link
                                            v-if="sub.status_badge !== 'active' && sub.status_badge !== 'renewed'"
                                            :href="route('subscriptions.renewForm', sub.id)"
                                            class="inline-flex items-center rounded-md border border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white transition-colors"
                                        >Renew</Link>
                                        <Link
                                            :href="route('subscriptions.edit', sub.id)"
                                            class="inline-flex items-center rounded-md border border-blue-500 bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-500 hover:text-white transition-colors"
                                        >Edit</Link>
                                        <button
                                            v-if="canDelete"
                                            @click="confirmDelete(sub.id)"
                                            class="inline-flex items-center rounded-md border border-destructive bg-destructive/10 px-2.5 py-1 text-xs font-medium text-destructive hover:bg-destructive hover:text-white transition-colors"
                                        >Delete</button>
                                    </template>
                                    <button
                                        v-else-if="canRestore"
                                        @click="confirmRestore(sub.id)"
                                        class="inline-flex items-center rounded-md border border-amber-500 bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-600 dark:text-amber-400 hover:bg-amber-500 hover:text-white transition-colors"
                                    >Restore</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.subscriptions.data.length === 0">
                            <td colspan="8" class="py-8 text-center text-muted-foreground">
                                No subscriptions found matching filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.subscriptions.links && props.subscriptions.links.length > 3"
                class="flex items-center justify-between border-t border-border bg-card px-4 py-3 sm:px-6"
            >
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Showing
                            <span class="font-medium text-foreground">{{ props.subscriptions.from }}</span>
                            to
                            <span class="font-medium text-foreground">{{ props.subscriptions.to }}</span>
                            of
                            <span class="font-medium text-foreground">{{ props.subscriptions.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <Link
                                v-for="(link, key) in props.subscriptions.links"
                                :key="key"
                                :href="link.url || ''"
                                v-html="link.label"
                                preserve-scroll
                                class="relative inline-flex items-center border px-4 py-2 text-sm font-medium focus:z-20"
                                :class="[
                                    link.active ? 'z-10 border-primary bg-primary/10 text-primary' : 'border-input bg-background text-muted-foreground hover:bg-muted',
                                    !link.url ? 'pointer-events-none text-muted-foreground opacity-50' : '',
                                ]"
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
