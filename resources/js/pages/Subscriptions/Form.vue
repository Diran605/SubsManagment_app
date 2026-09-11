<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, watch, onMounted } from 'vue';

const props = defineProps<{
    subscription?: any;
    renewing?: any;
    businesses: any[];
    services: any[];
    defaultBusinessId?: number | null;
}>();

const isEditing = !!props.subscription;
const isRenewing = !!props.renewing;
const sourceData = props.subscription || props.renewing || {};

const form = useForm({
    business_id: sourceData.business_id || props.defaultBusinessId || '',
    service_id: sourceData.service_id || '',
    package_id: sourceData.package_id || '',
    modules: sourceData.modules?.map((m: any) => m.id) || [],
    mode: sourceData.mode || '',
    amount: sourceData.amount || '',
    start_date: sourceData.start_date || '',
    end_date: sourceData.end_date || '',
    notes: sourceData.notes || '',
});

const selectedService = computed(() => {
    return props.services.find((s) => s.id === form.service_id);
});

const availablePackages = computed(() => {
    return selectedService.value?.packages || [];
});

const availableModules = computed(() => {
    return selectedService.value?.modules || [];
});

// Auto-suggest base price when service or mode changes
watch([() => form.service_id, () => form.mode, () => form.package_id], ([newServiceId, newMode, newPackageId]) => {
    if (newServiceId && !isEditing && !isRenewing) {
        let basePrice = 0;
        
        if (newPackageId) {
             const pkg = availablePackages.value.find((p: any) => p.id === newPackageId);
             if (pkg && pkg.reference_price) {
                 basePrice = parseFloat(pkg.reference_price);
             }
        } else if (newMode) {
            const service = props.services.find((s) => s.id === newServiceId);
            if (service && service.pricing) {
                const pricing = service.pricing.find(
                    (p: any) => p.mode === newMode,
                );
                if (pricing) {
                    basePrice = parseFloat(pricing.base_price);
                }
            }
        }
        
        if (basePrice > 0) {
             form.amount = basePrice.toString();
        }
    }
});

watch(() => form.package_id, (newPackageId) => {
    if (newPackageId) {
        const pkg = availablePackages.value.find((p: any) => p.id === newPackageId);
        if (pkg && pkg.modules) {
            // Auto-select modules from package
            const moduleIds = pkg.modules.map((m: any) => m.id);
            form.modules = [...new Set([...form.modules, ...moduleIds])];
        }
    }
});

const submit = () => {
    if (isEditing) {
        form.put(route('subscriptions.update', props.subscription.id));
    } else if (isRenewing) {
        form.post(route('subscriptions.renewForm', props.renewing.id));
    } else {
        form.post(route('subscriptions.store'));
    }
};

const title = computed(() => {
    if (isEditing) return 'Edit Subscription';
    if (isRenewing)
        return `Renew Subscription: ${props.renewing.business?.name}`;
    return 'New Subscription';
});
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
            </div>
            <Link
                :href="'/dashboard'"
                class="text-sm font-medium text-gray-500 hover:text-gray-700"
            >
                &larr; Back to Dashboard
            </Link>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Select Business & Service -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Business <span class="text-red-500">*</span></label
                        >
                        <select
                            v-model="form.business_id"
                            required
                            :disabled="isRenewing"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 sm:text-sm"
                        >
                            <option value="">Select Business</option>
                            <option
                                v-for="b in props.businesses"
                                :key="b.id"
                                :value="b.id"
                            >
                                {{ b.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.business_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.business_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Service <span class="text-red-500">*</span></label
                        >
                        <select
                            v-model="form.service_id"
                            required
                            :disabled="isRenewing"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 sm:text-sm"
                        >
                            <option value="">Select Service</option>
                            <option
                                v-for="s in props.services"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.service_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.service_id }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Mode <span class="text-red-500">*</span></label
                        >
                        <select
                            v-model="form.mode"
                            required
                            :disabled="isRenewing"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 sm:text-sm"
                        >
                            <option value="">Select Mode</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                        <p
                            v-if="form.errors.mode"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.mode }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Amount (FCFA)
                            <span class="text-red-500">*</span></label
                        >
                        <input
                            v-model="form.amount"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        />
                        <p
                            v-if="form.errors.amount"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.amount }}
                        </p>
                    </div>
                </div>

                <div v-if="form.service_id && (availablePackages.length > 0 || availableModules.length > 0)" class="rounded-lg border border-gray-100 bg-gray-50 p-4 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900">Service Configuration</h3>
                    
                    <div v-if="availablePackages.length > 0">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Select Package (Optional)</label>
                        <select v-model="form.package_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Custom Configuration</option>
                            <option v-for="pkg in availablePackages" :key="pkg.id" :value="pkg.id">
                                {{ pkg.name }} ({{ pkg.reference_price ? pkg.reference_price + ' FCFA' : 'Custom Pricing' }})
                            </option>
                        </select>
                        <p v-if="form.errors.package_id" class="mt-1 text-sm text-red-600">{{ form.errors.package_id }}</p>
                    </div>

                    <div v-if="availableModules.length > 0">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Included Modules</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label v-for="mod in availableModules" :key="mod.id" class="flex items-start space-x-3 rounded-lg border border-gray-200 bg-white p-3 hover:bg-gray-50 cursor-pointer">
                                <div class="flex h-5 items-center">
                                    <input 
                                        type="checkbox" 
                                        :value="mod.id" 
                                        v-model="form.modules"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                    />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">{{ mod.name }}</span>
                                    <span v-if="mod.description" class="text-xs text-gray-500">{{ mod.description }}</span>
                                </div>
                            </label>
                        </div>
                        <p v-if="form.errors.modules" class="mt-1 text-sm text-red-600">{{ form.errors.modules }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Start Date
                            <span class="text-red-500">*</span></label
                        >
                        <input
                            v-model="form.start_date"
                            type="date"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        />
                        <p
                            v-if="form.errors.start_date"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.start_date }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >End Date <span class="text-red-500">*</span></label
                        >
                        <input
                            v-model="form.end_date"
                            type="date"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        />
                        <p
                            v-if="form.errors.end_date"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.end_date }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Notes</label
                    >
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    ></textarea>
                    <p
                        v-if="form.errors.notes"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.notes }}
                    </p>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{
                            isEditing
                                ? 'Update Subscription'
                                : isRenewing
                                  ? 'Confirm Renewal'
                                  : 'Save Subscription'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
