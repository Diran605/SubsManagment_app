<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canCreate = computed(() => page.props.auth?.user?.permissions?.includes('create services'));
const canEdit = computed(() => page.props.auth?.user?.permissions?.includes('edit services'));
const canDelete = computed(() => page.props.auth?.user?.permissions?.includes('delete services'));

const props = defineProps<{
    services: any[];
}>();

import { useForm } from '@inertiajs/vue3';
const deleteForm = useForm({});
const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this service?')) {
        deleteForm.delete(route('services.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Services" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Services</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Manage the services you offer.
                </p>
            </div>
            <Link
                v-if="canCreate"
                :href="route('services.create')"
                :class="buttonVariants({ variant: 'default' })"
            >
                Add Service
            </Link>
        </div>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
        >
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-border bg-orange-500 text-white"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Service Name</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">Configuration</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="service in props.services"
                            :key="service.id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 font-medium text-foreground">
                                <div class="flex items-center gap-2">
                                    <span>{{ service.name }}</span>
                                    <span v-if="service.category === 'education'" class="inline-flex items-center rounded-md bg-blue-500/10 px-2 py-0.5 text-[10px] font-semibold text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                        🎓 Education
                                    </span>
                                </div>
                            </td>
                            <td
                                class="max-w-xs truncate px-4 py-3 text-muted-foreground"
                            >
                                {{ service.description || '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <span class="rounded bg-primary/10 px-2 py-1 text-xs text-primary border border-primary/20">
                                        {{ service.packages?.length || 0 }} Packages
                                    </span>
                                    <span class="rounded bg-muted px-2 py-1 text-xs text-muted-foreground border border-border">
                                        {{ service.modules?.length || 0 }} Modules
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link
                                        :href="route('services.show', service.id)"
                                        class="inline-flex items-center rounded-md border border-primary bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary hover:text-white transition-colors dark:border-primary dark:text-primary"
                                        >View</Link
                                    >
                                    <Link
                                        v-if="canEdit"
                                        :href="route('services.edit', service.id)"
                                        class="inline-flex items-center rounded-md border border-blue-500 bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-500 hover:text-white transition-colors"
                                        >Edit</Link
                                    >
                                    <button
                                        v-if="canDelete"
                                        @click="confirmDelete(service.id)"
                                        class="inline-flex items-center rounded-md border border-destructive bg-destructive/10 px-2.5 py-1 text-xs font-medium text-destructive hover:bg-destructive hover:text-white transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.services.length === 0">
                            <td
                                colspan="4"
                                class="py-8 text-center text-muted-foreground"
                            >
                                No services found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
