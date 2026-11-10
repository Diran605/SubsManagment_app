<script setup lang="ts">
import staffRoutes from '@/routes/staff';
import businessesRoutes from '@/routes/businesses';
import { Head, Link } from '@inertiajs/vue3';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    staff: any;
}>();
</script>

<template>
    <Head :title="staff.name" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1
                    class="text-foreground text-2xl font-semibold tracking-tight md:text-3xl"
                >
                    {{ staff.name }}
                </h1>
                <p class="text-muted-foreground mt-1 text-sm">
                    Staff Details & Audit Log
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="staffRoutes.index.url()"
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    Back to List
                </Link>
                <Link
                    :href="staffRoutes.edit.url(staff.id)"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    Edit
                </Link>
                <Link
                    :href="staffRoutes.toggle.url(staff.id)"
                    method="patch"
                    as="button"
                    :class="buttonVariants({ variant: 'secondary' })"
                    class="text-warning hover:bg-warning/10 font-medium"
                >
                    {{ staff.is_active ? 'Deactivate' : 'Activate' }}
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div
                class="border-border bg-card col-span-1 h-fit space-y-6 rounded-xl border p-6 shadow-sm"
            >
                <dl class="space-y-4">
                    <div>
                        <dt class="text-muted-foreground text-sm font-medium">
                            Email
                        </dt>
                        <dd class="text-foreground mt-1 text-sm">
                            {{ staff.email }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground text-sm font-medium">
                            Status
                        </dt>
                        <dd class="mt-1">
                            <span
                                :class="[
                                    'rounded-md border px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                    staff.is_active
                                        ? 'bg-success/10 text-success ring-success/20 border-transparent'
                                        : 'bg-destructive/10 text-destructive ring-destructive/20 border-transparent',
                                ]"
                            >
                                {{ staff.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground text-sm font-medium">
                            Joined
                        </dt>
                        <dd class="text-foreground mt-1 text-sm">
                            {{
                                new Date(staff.created_at).toLocaleDateString()
                            }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="col-span-2 space-y-6">
                <!-- Businesses Created -->
                <div
                    class="border-border bg-card flex flex-col rounded-xl border shadow-xs"
                >
                    <div class="border-border bg-muted/20 border-b p-4">
                        <h3 class="text-foreground font-semibold">
                            Businesses Added
                        </h3>
                    </div>
                    <div class="p-4" v-if="staff.businesses?.length === 0">
                        <p class="text-muted-foreground text-sm">
                            No businesses added by this staff member.
                        </p>
                    </div>
                    <ul class="divide-border divide-y" v-else>
                        <li
                            v-for="b in staff.businesses"
                            :key="b.id"
                            class="hover:bg-muted/50 p-4 transition-colors"
                        >
                            <Link
                                :href="businessesRoutes.show.url(b.id)"
                                class="text-primary text-sm font-medium hover:underline"
                                >{{ b.name }}</Link
                            >
                            <span class="text-muted-foreground ml-2 text-xs"
                                >on
                                {{
                                    new Date(b.created_at).toLocaleDateString()
                                }}</span
                            >
                        </li>
                    </ul>
                </div>

                <!-- Subscriptions Created -->
                <div
                    class="border-border bg-card flex flex-col rounded-xl border shadow-xs"
                >
                    <div class="border-border bg-muted/20 border-b p-4">
                        <h3 class="text-foreground font-semibold">
                            Subscriptions Handled
                        </h3>
                    </div>
                    <div class="p-4" v-if="staff.subscriptions?.length === 0">
                        <p class="text-muted-foreground text-sm">
                            No subscriptions handled by this staff member.
                        </p>
                    </div>
                    <ul class="divide-border divide-y" v-else>
                        <li
                            v-for="sub in staff.subscriptions"
                            :key="sub.id"
                            class="hover:bg-muted/50 flex items-center justify-between p-4 transition-colors"
                        >
                            <div>
                                <span
                                    class="text-foreground text-sm font-medium capitalize"
                                    >{{ sub.mode }}</span
                                >
                                <span class="text-muted-foreground ml-2 text-xs"
                                    >from
                                    {{
                                        new Date(
                                            sub.start_date,
                                        ).toLocaleDateString('en-GB', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric',
                                        })
                                    }}
                                    to
                                    {{
                                        new Date(
                                            sub.end_date,
                                        ).toLocaleDateString('en-GB', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric',
                                        })
                                    }}</span
                                >
                            </div>
                            <span class="text-foreground text-sm font-medium"
                                >{{
                                    Number(sub.amount).toLocaleString('en-US')
                                }}
                                FCFA</span
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
