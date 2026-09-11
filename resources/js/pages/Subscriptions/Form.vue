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
    mode: sourceData.mode || '',
    amount: sourceData.amount || '',
    start_date: sourceData.start_date || '',
    end_date: sourceData.end_date || '',
    notes: sourceData.notes || '',
});

// Auto-suggest base price when service or mode changes
watch([() => form.service_id, () => form.mode], ([newServiceId, newMode]) => {
    if (newServiceId && newMode && !isEditing && !isRenewing) {
        // don't override on edit/renew unless they change it manually
        const service = props.services.find((s) => s.id === newServiceId);
        if (service && service.pricing) {
            const pricing = service.pricing.find(
                (p: any) => p.mode === newMode,
            );
            if (pricing) {
                form.amount = pricing.base_price;
            }
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
