<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    staff?: any;
    roles?: any[];
}>();

const isEditing = !!props.staff;

const form = useForm({
    name: props.staff?.name || '',
    email: props.staff?.email || '',
    password: '',
    password_confirmation: '',
    is_active: props.staff ? props.staff.is_active : true,
    roles: props.staff?.roles ? props.staff.roles.map((r: any) => r.name) : [] as string[],
});

const submit = () => {
    if (isEditing) {
        form.put(route('staff.update', props.staff.id));
    } else {
        form.post(route('staff.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Staff' : 'Create Staff'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ isEditing ? 'Edit Staff' : 'Create Staff' }}
                </h1>
            </div>
            <Link
                :href="route('staff.index')"
                class="text-sm font-medium text-gray-500 hover:text-gray-700"
            >
                &larr; Back to List
            </Link>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Name <span class="text-red-500">*</span></label
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
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Email <span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Password
                            <span v-if="!isEditing" class="text-red-500"
                                >*</span
                            >
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            :required="!isEditing"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        />
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                        <p v-if="isEditing" class="mt-1 text-xs text-gray-500">
                            Leave blank to keep current password
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Confirm Password
                            <span v-if="!isEditing" class="text-red-500"
                                >*</span
                            >
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            :required="!isEditing"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        />
                    </div>
                </div>

                <div class="flex items-center pt-2">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <label
                        for="is_active"
                        class="ml-2 block text-sm text-gray-900"
                    >
                        Active Account
                    </label>
                </div>

                <div v-if="roles && roles.length > 0" class="pt-4 border-t border-gray-100">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Assign Roles</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
                            <input 
                                type="checkbox"
                                :value="role.name"
                                v-model="form.roles"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-sm font-medium text-gray-900">{{ role.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Staff' : 'Save Staff' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
