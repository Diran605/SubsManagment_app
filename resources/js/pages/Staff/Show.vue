<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link } from '@inertiajs/vue3';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    staff: any;
}>();
</script>

<template>
    <Head :title="staff.name" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    {{ staff.name }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Staff Details & Audit Log
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('staff.index')"
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    Back to List
                </Link>
                <Link
                    :href="route('staff.edit', staff.id)"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    Edit
                </Link>
                <Link
                    :href="route('staff.toggle', staff.id)"
                    method="patch"
                    as="button"
                    :class="buttonVariants({ variant: 'secondary' })"
                    class="font-medium text-amber-600 dark:text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-900/30"
                >
                    {{ staff.is_active ? 'Deactivate' : 'Activate' }}
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div
                class="col-span-1 h-fit space-y-6 rounded-2xl border border-border bg-card p-6 shadow-sm"
            >
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Email</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ staff.email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                        <dd class="mt-1">
                            <span
                                :class="[
                                    'rounded-md border px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                    staff.is_active
                                        ? 'border-transparent bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 ring-emerald-500/20'
                                        : 'border-transparent bg-destructive/10 text-destructive ring-destructive/20',
                                ]"
                            >
                                {{ staff.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted-foreground">Joined</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ new Date(staff.created_at).toLocaleDateString() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="col-span-2 space-y-6">
                <!-- Businesses Created -->
                <div class="flex flex-col rounded-2xl border border-border bg-card shadow-sm">
                    <div class="border-b border-border p-4 bg-muted/20">
                        <h3 class="font-semibold text-foreground">Businesses Added</h3>
                    </div>
                    <div class="p-4" v-if="staff.businesses?.length === 0">
                        <p class="text-sm text-muted-foreground">No businesses added by this staff member.</p>
                    </div>
                    <ul class="divide-y divide-border" v-else>
                        <li v-for="b in staff.businesses" :key="b.id" class="p-4 transition-colors hover:bg-muted/50">
                            <Link :href="route('businesses.show', b.id)" class="text-sm font-medium text-primary hover:underline">{{ b.name }}</Link>
                            <span class="ml-2 text-xs text-muted-foreground">on {{ new Date(b.created_at).toLocaleDateString() }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Subscriptions Created -->
                <div class="flex flex-col rounded-2xl border border-border bg-card shadow-sm">
                    <div class="border-b border-border p-4 bg-muted/20">
                        <h3 class="font-semibold text-foreground">Subscriptions Handled</h3>
                    </div>
                    <div class="p-4" v-if="staff.subscriptions?.length === 0">
                        <p class="text-sm text-muted-foreground">No subscriptions handled by this staff member.</p>
                    </div>
                    <ul class="divide-y divide-border" v-else>
                        <li v-for="sub in staff.subscriptions" :key="sub.id" class="p-4 flex items-center justify-between transition-colors hover:bg-muted/50">
                            <div>
                                <span class="text-sm font-medium text-foreground capitalize">{{ sub.mode }}</span>
                                <span class="ml-2 text-xs text-muted-foreground">from {{ new Date(sub.start_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }} to {{ new Date(sub.end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}</span>
                            </div>
                            <span class="text-sm font-medium text-foreground">{{ Number(sub.amount).toLocaleString('en-US') }} FCFA</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
