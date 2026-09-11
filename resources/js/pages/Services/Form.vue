<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps<{
    service?: any;
}>();

const isEditing = !!props.service;

const form = useForm({
    name: props.service?.name || '',
    description: props.service?.description || '',
    prices: [
        { mode: 'online', base_price: 0 },
        { mode: 'offline', base_price: 0 },
        { mode: 'hybrid', base_price: 0 },
    ],
});

onMounted(() => {
    if (isEditing && props.service?.pricing) {
        props.service.pricing.forEach((p: any) => {
            const index = form.prices.findIndex((fp) => fp.mode === p.mode);
            if (index !== -1) {
                form.prices[index].base_price = parseFloat(p.base_price);
            }
        });
    }
});

const submit = () => {
    if (isEditing) {
        form.put(route('services.update', props.service.id));
    } else {
        form.post(route('services.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Service' : 'Create Service'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ isEditing ? 'Edit Service' : 'Create Service' }}
                </h1>
            </div>
            <Link
                :href="route('services.index')"
                class="text-sm font-medium text-gray-500 hover:text-gray-700"
            >
                &larr; Back to List
            </Link>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Service Name
                            <span class="text-red-500">*</span></label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Description</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        ></textarea>
                        <p
                            v-if="form.errors.description"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Base Pricing
                    </h3>
                    <p class="mb-4 text-sm text-gray-500">
                        Enter a base price for each mode. Leave at 0 if the mode
                        is not supported for this service.
                    </p>

                    <div class="space-y-3">
                        <div
                            v-for="(price, index) in form.prices"
                            :key="price.mode"
                            class="flex items-center gap-4"
                        >
                            <label
                                class="w-24 text-sm font-medium text-gray-700 capitalize"
                                >{{ price.mode }}</label
                            >
                            <div class="relative flex-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500"
                                    >FCFA</span
                                >
                                <input
                                    v-model="form.prices[index].base_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-12 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Service' : 'Save Service' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
