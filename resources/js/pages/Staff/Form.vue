<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { buttonVariants } from '@/components/ui/button';

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

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    {{ isEditing ? 'Edit Staff' : 'Create Staff' }}
                </h1>
            </div>
            <Link
                :href="route('staff.index')"
                class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
            >
                &larr; Back to List
            </Link>
        </div>

        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground"
                        >Name <span class="text-destructive">*</span></label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-md border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary"
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-destructive"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground"
                        >Email <span class="text-destructive">*</span></label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full rounded-md border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-sm text-destructive"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-foreground"
                        >
                            Password
                            <span v-if="!isEditing" class="text-destructive"
                                >*</span
                            >
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            :required="!isEditing"
                            class="w-full rounded-md border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary"
                        />
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.password }}
                        </p>
                        <p v-if="isEditing" class="mt-1.5 text-xs text-muted-foreground">
                            Leave blank to keep current password
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-foreground"
                        >
                            Confirm Password
                            <span v-if="!isEditing" class="text-destructive"
                                >*</span
                            >
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            :required="!isEditing"
                            class="w-full rounded-md border-input bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:ring-primary"
                        />
                    </div>
                </div>

                <div class="flex items-center pt-2">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-input bg-background text-primary focus:ring-primary"
                    />
                    <label
                        for="is_active"
                        class="ml-2 block text-sm font-medium text-foreground"
                    >
                        Active Account
                    </label>
                </div>

                <div v-if="roles && roles.length > 0" class="pt-4 border-t border-border">
                    <label class="mb-3 block text-sm font-medium text-foreground">Assign Roles</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
                            <input 
                                type="checkbox"
                                :value="role.name"
                                v-model="form.roles"
                                class="h-4 w-4 rounded border-input bg-background text-primary focus:ring-primary"
                            />
                            <span class="text-sm font-medium text-foreground">{{ role.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="buttonVariants({ variant: 'default' })"
                        class="disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Staff' : 'Save Staff' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
