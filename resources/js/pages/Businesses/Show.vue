<script setup lang="ts">
import { table } from '@/lib/ui';
import businessesRoutes from '@/routes/businesses';
import subscriptionsRoutes from '@/routes/subscriptions';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canEdit = computed(() =>
    page.props.auth?.user?.permissions?.includes('edit businesses'),
);
const canDelete = computed(() =>
    page.props.auth?.user?.permissions?.includes('delete businesses'),
);

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
                <h1
                    class="text-foreground text-2xl font-semibold tracking-tight md:text-3xl"
                >
                    {{ business.name }}
                </h1>
                <p class="text-muted-foreground mt-1 text-sm">
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
                class="border-border bg-card col-span-1 h-fit rounded-xl border p-6 shadow-sm"
            >
                <h3
                    class="border-border text-foreground mb-4 border-b pb-3 font-semibold"
                >
                    Contact Information
                </h3>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-muted-foreground font-medium">
                            Contact Person
                        </dt>
                        <dd class="text-foreground mt-1">
                            {{ business.contact_person || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground font-medium">Email</dt>
                        <dd class="text-foreground mt-1">
                            {{ business.email || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground font-medium">Phone</dt>
                        <dd class="text-foreground mt-1">
                            {{ business.phone || 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground font-medium">
                            Address
                        </dt>
                        <dd class="text-foreground mt-1">
                            {{ business.address || 'N/A' }}
                        </dd>
                    </div>
                    <div class="border-border mt-4 border-t pt-4">
                        <dt class="text-muted-foreground text-xs">Added By</dt>
                        <dd class="text-foreground mt-1 text-xs font-medium">
                            {{ business.creator?.name || 'Unknown' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Subscriptions History -->
            <div
                class="border-border bg-card col-span-2 flex flex-col overflow-hidden rounded-xl border shadow-xs"
            >
                <div
                    class="border-border bg-muted/20 flex items-center justify-between border-b p-4"
                >
                    <h3 class="text-foreground font-semibold">Subscriptions</h3>
                    <Link
                        :href="
                            subscriptionsRoutes.create.url({
                                query: { business_id: business.id },
                            })
                        "
                        class="text-primary text-sm font-medium hover:underline"
                    >
                        + New Subscription
                    </Link>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-border bg-muted/50 text-muted-foreground border-b"
                        >
                            <tr>
                                <th class="px-4 py-3 font-medium">Service</th>
                                <th class="px-4 py-3 font-medium">Package</th>
                                <th class="px-4 py-3 font-medium">Mode</th>
                                <th class="px-4 py-3 font-medium">Period</th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-border divide-y">
                            <tr
                                v-for="sub in business.subscriptions"
                                :key="sub.id"
                                :class="table.row"
                            >
                                <td
                                    class="text-foreground px-4 py-3 font-medium"
                                >
                                    {{ sub.service?.name }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="sub.package"
                                        class="bg-info/10 text-info ring-info/20 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    >
                                        {{ sub.package.name }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-muted-foreground text-xs"
                                        >Custom</span
                                    >
                                    <div
                                        v-if="
                                            sub.modules &&
                                            sub.modules.length > 0
                                        "
                                        class="mt-1.5 flex flex-wrap gap-1"
                                    >
                                        <span
                                            v-for="mod in sub.modules"
                                            :key="mod.id"
                                            class="bg-muted text-muted-foreground border-border inline-flex items-center rounded-full border px-1.5 py-0.5 text-[10px] font-medium"
                                        >
                                            {{ mod.name }}
                                        </span>
                                    </div>
                                </td>
                                <td
                                    class="text-muted-foreground px-4 py-3 capitalize"
                                >
                                    {{ sub.mode }}
                                </td>
                                <td
                                    class="text-muted-foreground px-4 py-3 text-xs whitespace-nowrap"
                                >
                                    {{
                                        new Date(
                                            sub.start_date,
                                        ).toLocaleDateString('en-GB', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric',
                                        })
                                    }}
                                    <br /><span class="text-muted-foreground/50"
                                        >↓</span
                                    ><br />
                                    {{
                                        new Date(
                                            sub.end_date,
                                        ).toLocaleDateString('en-GB', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric',
                                        })
                                    }}
                                </td>
                                <td
                                    class="text-foreground px-4 py-3 text-right font-medium whitespace-nowrap"
                                >
                                    {{
                                        Number(sub.amount).toLocaleString(
                                            'en-US',
                                        )
                                    }}
                                    FCFA
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !business.subscriptions ||
                                    business.subscriptions.length === 0
                                "
                            >
                                <td colspan="5" :class="table.empty">
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
