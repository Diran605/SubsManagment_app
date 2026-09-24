<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    business?: {
        id: number;
        name: string;
        contact_person: string | null;
        phone: string | null;
        email: string | null;
        address: string | null;
    };
}>();

const isEditing = !!props.business;

const form = useForm({
    name: props.business?.name || '',
    contact_person: props.business?.contact_person || '',
    phone: props.business?.phone || '',
    email: props.business?.email || '',
    address: props.business?.address || '',
});

const submit = () => {
    if (isEditing) {
        form.put(route('businesses.update', props.business!.id));
    } else {
        form.post(route('businesses.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Business' : 'Create Business'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    {{ isEditing ? 'Edit Business' : 'Create Business' }}
                </h1>
            </div>
            <Link
                :href="route('businesses.index')"
                class="text-sm font-medium text-muted-foreground hover:text-foreground"
            >
                &larr; Back to List
            </Link>
        </div>

        <div class="rounded-2xl border border-border bg-card p-6 md:p-8 shadow-sm">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground"
                        >Business Name
                        <span class="text-destructive">*</span></label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-destructive"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Contact Person</label
                        >
                        <input
                            v-model="form.contact_person"
                            type="text"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                        />
                        <p
                            v-if="form.errors.contact_person"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.contact_person }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Phone</label
                        >
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground"
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-sm text-destructive"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground"
                        >Address</label
                    >
                    <textarea
                        v-model="form.address"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                    ></textarea>
                    <p
                        v-if="form.errors.address"
                        class="mt-1 text-sm text-destructive"
                    >
                        {{ form.errors.address }}
                    </p>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="buttonVariants({ variant: 'default' })"
                        class="disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Business' : 'Save Business' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
