<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canEdit = computed(() => page.props.auth?.user?.permissions?.includes('edit services'));
const canDelete = computed(() => page.props.auth?.user?.permissions?.includes('delete services'));
const canCreatePackages = computed(() => page.props.auth?.user?.permissions?.includes('create packages'));
const canEditPackages = computed(() => page.props.auth?.user?.permissions?.includes('edit packages'));
const canDeletePackages = computed(() => page.props.auth?.user?.permissions?.includes('delete packages'));
const canCreateModules = computed(() => page.props.auth?.user?.permissions?.includes('create modules'));
const canEditModules = computed(() => page.props.auth?.user?.permissions?.includes('edit modules'));
const canDeleteModules = computed(() => page.props.auth?.user?.permissions?.includes('delete modules'));

const props = defineProps<{
    service: any;
}>();

const deleteForm = useForm({});

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this service?')) {
        deleteForm.delete(route('services.destroy', props.service.id));
    }
};

const moduleForm = useForm({
    name: '',
    description: '',
});

const submitModule = () => {
    moduleForm.post(route('services.modules.store', props.service.id), {
        preserveScroll: true,
        onSuccess: () => moduleForm.reset(),
    });
};

const packageForm = useForm({
    name: '',
    description: '',
    online_price: '',
    offline_price: '',
    hybrid_price: '',
    modules: [],
});

const submitPackage = () => {
    packageForm.post(route('services.packages.store', props.service.id), {
        preserveScroll: true,
        onSuccess: () => packageForm.reset(),
    });
};

const deleteModuleForm = useForm({});
const deleteModule = (id: number) => {
    if (confirm('Are you sure you want to delete this module?')) {
        deleteModuleForm.delete(route('modules.destroy', id), {
            preserveScroll: true
        });
    }
};

const deletePackageForm = useForm({});
const deletePackage = (id: number) => {
    if (confirm('Are you sure you want to delete this package?')) {
        deletePackageForm.delete(route('packages.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head :title="service.name" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    {{ service.name }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Service Details & Active Subscriptions
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('services.index')"
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    Back to List
                </Link>
                <Link
                    v-if="canEdit"
                    :href="route('services.edit', service.id)"
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
            <!-- Service Info -->
            <div
                class="col-span-1 h-fit space-y-6 rounded-2xl border border-border bg-card p-6 shadow-sm"
            >
                <div>
                    <h3 class="mb-2 font-semibold text-foreground">
                        Description
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ service.description || 'No description provided.' }}
                    </p>
                </div>

                <div class="mt-4 border-t border-border pt-4">
                    <dt class="text-xs text-muted-foreground">Added By</dt>
                    <dd class="mt-1 text-xs text-foreground font-medium">
                        {{ service.creator?.name || 'Unknown' }}
                    </dd>
                </div>
            </div>

            <!-- Subscriptions using this Service -->
            <div
                class="col-span-2 flex flex-col rounded-2xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-border p-4"
                >
                    <h3 class="font-semibold text-foreground">
                        Subscribed Businesses
                    </h3>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b border-border bg-muted/50 text-muted-foreground"
                        >
                            <tr>
                                <th class="px-4 py-3 font-medium">Business</th>
                                <th class="px-4 py-3 font-medium">Mode</th>
                                <th class="px-4 py-3 font-medium">End Date</th>
                                <th class="px-4 py-3 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="sub in service.subscriptions"
                                :key="sub.id"
                                class="transition-colors hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 font-medium text-foreground">
                                    <Link
                                        :href="route('businesses.show', sub.business_id)"
                                        class="text-primary hover:underline"
                                        >{{ sub.business?.name }}</Link
                                    >
                                </td>
                                <td class="px-4 py-3 text-muted-foreground capitalize">
                                    {{ sub.mode }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ sub.end_date }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium text-foreground"
                                >
                                    {{ Number(sub.amount).toLocaleString('en-US') }} FCFA
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !service.subscriptions ||
                                    service.subscriptions.length === 0
                                "
                            >
                                <td
                                    colspan="4"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    No active subscriptions for this service.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Modules & Packages Configuration (Full Width) -->
            <div class="col-span-1 md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Modules -->
                <div class="rounded-2xl border border-border bg-card shadow-sm flex flex-col">
                    <div class="flex items-center justify-between border-b border-border p-4">
                        <h3 class="font-semibold text-foreground">Service Modules</h3>
                    </div>
                    <div class="p-4 flex-1">
                        <ul class="space-y-3">
                            <li v-for="mod in service.modules" :key="mod.id" class="flex justify-between items-center border-b border-border pb-3 last:border-0 last:pb-0 group">
                                <div>
                                    <div class="font-medium text-foreground text-sm">{{ mod.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ mod.description || 'No description' }}</div>
                                </div>
                                <button v-if="canDeleteModules" @click="deleteModule(mod.id)" class="text-destructive hover:text-destructive/80 opacity-0 group-hover:opacity-100 transition-opacity p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </li>
                            <li v-if="!service.modules || service.modules.length === 0" class="text-sm text-muted-foreground text-center py-4">
                                No modules configured.
                            </li>
                        </ul>
                    </div>
                    <div v-if="canCreateModules" class="border-t border-border p-4 bg-muted/30 rounded-b-2xl">
                        <form @submit.prevent="submitModule" class="space-y-3">
                            <h4 class="text-sm font-medium text-foreground">Add New Module</h4>
                            <div>
                                <input v-model="moduleForm.name" type="text" placeholder="Module Name" required class="w-full rounded-md border-input bg-background px-3 py-2 text-foreground text-sm focus:border-primary focus:ring-primary" />
                                <p v-if="moduleForm.errors.name" class="mt-1 text-xs text-destructive">{{ moduleForm.errors.name }}</p>
                            </div>
                            <div>
                                <input v-model="moduleForm.description" type="text" placeholder="Description (Optional)" class="w-full rounded-md border-input bg-background px-3 py-2 text-foreground text-sm focus:border-primary focus:ring-primary" />
                            </div>
                            <div v-if="deleteModuleForm.hasErrors" class="text-xs text-destructive">{{ deleteModuleForm.errors.module }}</div>
                            <button type="submit" :disabled="moduleForm.processing" :class="buttonVariants({ variant: 'secondary', class: 'w-full' })">Add Module</button>
                        </form>
                    </div>
                </div>

                <!-- Packages -->
                <div class="rounded-2xl border border-border bg-card shadow-sm flex flex-col">
                    <div class="flex items-center justify-between border-b border-border p-4">
                        <h3 class="font-semibold text-foreground">Subscription Packages</h3>
                    </div>
                    <div class="p-4 flex-1">
                        <ul class="space-y-4">
                            <li v-for="pkg in service.packages" :key="pkg.id" class="border border-border rounded-lg p-3 group relative bg-background/50">
                                <button v-if="canDeletePackages" @click="deletePackage(pkg.id)" class="absolute top-2 right-2 text-destructive hover:text-destructive/80 opacity-0 group-hover:opacity-100 transition-opacity p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                <div class="flex justify-between items-start mb-3 pr-6">
                                    <div>
                                        <div class="font-medium text-foreground text-sm">{{ pkg.name }}</div>
                                        <div class="text-xs text-muted-foreground mt-0.5">{{ pkg.description || 'No description' }}</div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1.5 mt-1">
                                        <span class="inline-flex items-center rounded-md bg-blue-500/10 px-2 py-0.5 text-[10px] font-medium text-blue-600 dark:text-blue-400 ring-1 ring-inset ring-blue-500/20">
                                            Online: {{ pkg.online_price ? Number(pkg.online_price).toLocaleString('en-US') + ' FCFA' : '-' }}
                                        </span>
                                        <span class="inline-flex items-center rounded-md bg-zinc-500/10 px-2 py-0.5 text-[10px] font-medium text-zinc-600 dark:text-zinc-400 ring-1 ring-inset ring-zinc-500/20">
                                            Offline: {{ pkg.offline_price ? Number(pkg.offline_price).toLocaleString('en-US') + ' FCFA' : '-' }}
                                        </span>
                                        <span class="inline-flex items-center rounded-md bg-purple-500/10 px-2 py-0.5 text-[10px] font-medium text-purple-600 dark:text-purple-400 ring-1 ring-inset ring-purple-500/20">
                                            Hybrid: {{ pkg.hybrid_price ? Number(pkg.hybrid_price).toLocaleString('en-US') + ' FCFA' : '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-[11px] font-medium text-muted-foreground block mb-1.5 uppercase tracking-wider">Includes</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span v-for="mod in pkg.modules" :key="mod.id" class="inline-flex items-center rounded-md bg-muted px-2 py-1 text-[11px] font-medium text-muted-foreground border border-border">
                                            {{ mod.name }}
                                        </span>
                                        <span v-if="!pkg.modules || pkg.modules.length === 0" class="text-xs text-muted-foreground/60 italic">None</span>
                                    </div>
                                </div>
                            </li>
                            <li v-if="!service.packages || service.packages.length === 0" class="text-sm text-muted-foreground text-center py-4">
                                No packages configured.
                            </li>
                        </ul>
                    </div>
                    <div v-if="canCreatePackages" class="border-t border-border p-4 bg-muted/30 rounded-b-2xl">
                        <form @submit.prevent="submitPackage" class="space-y-3">
                            <h4 class="text-sm font-medium text-foreground">Create New Package</h4>
                            <div v-if="deletePackageForm.hasErrors" class="text-xs text-destructive mb-2">{{ deletePackageForm.errors.package }}</div>
                            <div>
                                <input v-model="packageForm.name" type="text" placeholder="Package Name" required class="w-full rounded-md border-input bg-background px-3 py-2 text-foreground text-sm focus:border-primary focus:ring-primary" />
                                <p v-if="packageForm.errors.name" class="mt-1 text-xs text-destructive">{{ packageForm.errors.name }}</p>
                            </div>
                            <div>
                                <input v-model="packageForm.description" type="text" placeholder="Description (Optional)" class="w-full rounded-md border-input bg-background px-3 py-2 text-foreground text-sm focus:border-primary focus:ring-primary" />
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-muted-foreground mb-1">Online (FCFA)</label>
                                    <input v-model="packageForm.online_price" type="number" step="0.01" class="w-full rounded-md border-input bg-background px-3 py-2 text-foreground text-sm focus:border-primary focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="block text-xs text-muted-foreground mb-1">Offline (FCFA)</label>
                                    <input v-model="packageForm.offline_price" type="number" step="0.01" class="w-full rounded-md border-input bg-background px-3 py-2 text-foreground text-sm focus:border-primary focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="block text-xs text-muted-foreground mb-1">Hybrid (FCFA)</label>
                                    <input v-model="packageForm.hybrid_price" type="number" step="0.01" class="w-full rounded-md border-input bg-background px-3 py-2 text-foreground text-sm focus:border-primary focus:ring-primary" />
                                </div>
                            </div>
                            
                            <div v-if="service.modules && service.modules.length > 0">
                                <label class="block text-xs font-medium text-foreground mb-1.5 mt-3">Select Modules for Package</label>
                                <div class="max-h-32 overflow-y-auto border border-border bg-background rounded-md p-2 space-y-1">
                                    <label v-for="mod in service.modules" :key="mod.id" class="flex items-center space-x-2 text-sm cursor-pointer hover:bg-muted p-1.5 rounded transition-colors">
                                        <input type="checkbox" :value="mod.id" v-model="packageForm.modules" class="rounded border-input bg-background text-primary focus:ring-primary" />
                                        <span class="text-foreground">{{ mod.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" :disabled="packageForm.processing" :class="buttonVariants({ variant: 'secondary', class: 'w-full mt-4' })">Add Package</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
