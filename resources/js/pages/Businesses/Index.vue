<script setup lang="ts">
import { route } from 'ziggy-js';
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canCreate = computed(() => page.props.auth?.user?.permissions?.includes('create businesses'));
const canEdit = computed(() => page.props.auth?.user?.permissions?.includes('edit businesses'));
const canDelete = computed(() => page.props.auth?.user?.permissions?.includes('delete businesses'));

const props = defineProps<{
    businesses: any;
    filters: { search: string };
}>();

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(
        route('businesses.index'),
        { search: value },
        { preserveState: true, replace: true },
    );
});

import { useForm } from '@inertiajs/vue3';
const deleteForm = useForm({});
const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this business?')) {
        deleteForm.delete(route('businesses.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Businesses" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Businesses</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Manage your client businesses.
                </p>
            </div>
            <Link
                v-if="canCreate"
                :href="route('businesses.create')"
                :class="buttonVariants({ variant: 'default' })"
            >
                Add Business
            </Link>
        </div>

        <div
            class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
        >
            <div
                class="flex items-center justify-between border-b border-border bg-muted/30 p-4"
            >
                <input
                    type="text"
                    v-model="search"
                    placeholder="Search businesses..."
                    class="w-full max-w-sm rounded-md border border-input bg-background px-3 py-1.5 text-sm text-foreground focus:border-primary focus:ring-primary focus:outline-none"
                />
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-border bg-orange-500 text-white"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Contact Person</th>
                            <th class="px-4 py-3 font-medium">Phone</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="business in props.businesses.data"
                            :key="business.id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ business.name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ business.contact_person || '-' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ business.phone || '-' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ business.email || '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link
                                        :href="route('businesses.show', business.id)"
                                        class="inline-flex items-center rounded-md border border-primary bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary hover:text-white transition-colors dark:border-primary dark:text-primary"
                                        >View</Link
                                    >
                                    <Link
                                        v-if="canEdit"
                                        :href="route('businesses.edit', business.id)"
                                        class="inline-flex items-center rounded-md border border-blue-500 bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-500 hover:text-white transition-colors"
                                        >Edit</Link
                                    >
                                    <button
                                        v-if="canDelete"
                                        @click="confirmDelete(business.id)"
                                        class="inline-flex items-center rounded-md border border-destructive bg-destructive/10 px-2.5 py-1 text-xs font-medium text-destructive hover:bg-destructive hover:text-white transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.businesses.data.length === 0">
                            <td
                                colspan="5"
                                class="py-8 text-center text-muted-foreground"
                            >
                                No businesses found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="flex items-center justify-between border-t border-border bg-card px-4 py-3 sm:px-6"
                v-if="props.businesses.links.length > 3"
            >
                <div class="flex gap-1">
                    <Link
                        v-for="(link, k) in props.businesses.links"
                        :key="k"
                        :href="link.url || ''"
                        v-html="link.label"
                        :class="[
                            'rounded px-3 py-1 text-sm border font-medium',
                            link.active
                                ? 'z-10 border-primary bg-primary/10 text-primary'
                                : 'border-input bg-background text-muted-foreground hover:bg-muted',
                            !link.url ? 'pointer-events-none opacity-50' : '',
                        ]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
