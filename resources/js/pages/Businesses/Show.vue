<script setup lang="ts">
import { table } from '@/lib/ui';
import businessesRoutes from '@/routes/businesses';
import subscriptionsRoutes from '@/routes/subscriptions';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canEdit = computed(() => page.props.auth?.user?.permissions?.includes('edit businesses'));
const canDelete = computed(() => page.props.auth?.user?.permissions?.includes('delete businesses'));

const props = defineProps<{
    business: any;
}>();

const deleteForm = useForm({});

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this business?')) {
        deleteForm.delete(businessesRoutes.destroy.url(props.business.id));
    }
};
</script>

<template>
    <Head :title="business.name" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground md:text-3xl">
                    {{ business.name }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Business Details & Subscription History
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="businessesRoutes.index.url()"
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    Back to List
                </Link>
                <Link
                    v-if="canEdit"
                    :href="businessesRoutes.edit.url(business.id)"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    Edit
                </Link>
                <button
                    v-if="canDelete"
                    @click="confirmDelete"
                    :class="buttonVariants({ variant: 'destructive' })"
                >
                    Delete
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Business Info -->
            <div
                class="col-span-1 h-fit rounded-xl border border-border bg-card p-6 shadow-sm"
            >
                <h3 class="mb-4 border-b border-border pb-3 font-semibold text-foreground">
                    Contact Information
                </h3>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="font-medium text-muted-foreground">
                            Contact Person
                        </dt>
                        <dd class="mt-1 text-foreground">
                            {{ business.contact_person || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-muted-foreground">Email</dt>
                        <dd class="mt-1 text-foreground">
                            {{ business.email || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-muted-foreground">Phone</dt>
                        <dd class="mt-1 text-foreground">
                            {{ business.phone || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-muted-foreground">Address</dt>
                        <dd class="mt-1 text-foreground">
                            {{ business.address || 'N/A' }}
                        </dd>
                    </div>
                    <div class="mt-4 border-t border-border pt-4">
                        <dt class="text-xs text-muted-foreground">Added By</dt>
                        <dd class="mt-1 text-xs text-foreground font-medium">
                            {{ business.creator?.name || 'Unknown' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Subscriptions History -->
            <div
                class="col-span-2 flex flex-col rounded-xl border border-border bg-card shadow-xs overflow-hidden"
            >
                <div
                    class="flex items-center justify-between border-b border-border p-4 bg-muted/20"
                >
                    <h3 class="font-semibold text-foreground">Subscriptions</h3>
                    <Link
                        :href="subscriptionsRoutes.create.url({ query: { business_id: business.id } })"
                        class="text-sm font-medium text-primary hover:underline"
                    >
                        + New Subscription
                    </Link>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b border-border bg-muted/50 text-muted-foreground"
                        >
                            <tr>
                                <th class="px-4 py-3 font-medium">Service</th>
                                <th class="px-4 py-3 font-medium">Package</th>
                                <th class="px-4 py-3 font-medium">Mode</th>
                                <th class="px-4 py-3 font-medium">Period</th>
                                <th class="px-4 py-3 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="sub in business.subscriptions"
                                :key="sub.id"
                                :class="table.row"
                            >
                                <td class="px-4 py-3 font-medium text-foreground">
                                    {{ sub.service?.name }}
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="sub.package" class="inline-flex items-center rounded-md bg-info/10 px-2 py-1 text-xs font-medium text-info ring-1 ring-inset ring-info/20">
                                        {{ sub.package.name }}
                                    </span>
                                    <span v-else class="text-xs text-muted-foreground">Custom</span>
                                    <div v-if="sub.modules && sub.modules.length > 0" class="flex flex-wrap gap-1 mt-1.5">
                                        <span v-for="mod in sub.modules" :key="mod.id" class="inline-flex items-center rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground border border-border">
                                            {{ mod.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground capitalize">
                                    {{ sub.mode }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground text-xs whitespace-nowrap">
                                    {{ new Date(sub.start_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                    <br/><span class="text-muted-foreground/50">↓</span><br/>
                                    {{ new Date(sub.end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium text-foreground whitespace-nowrap"
                                >
                                    {{ Number(sub.amount).toLocaleString('en-US') }} FCFA
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !business.subscriptions ||
                                    business.subscriptions.length === 0
                                "
                            >
                                <td
                                    colspan="5"
                                    :class="table.empty"
                                >
                                    No subscriptions yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
