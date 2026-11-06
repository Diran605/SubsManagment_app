<script setup lang="ts">
import servicesRoutes from '@/routes/services';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    service?: any;
}>();

const isEditing = !!props.service;

const form = useForm({
    name: props.service?.name || '',
    category: props.service?.category || 'standard',
    description: props.service?.description || '',
});


const submit = () => {
    if (isEditing) {
        form.put(servicesRoutes.update.url(props.service.id));
    } else {
        form.post(servicesRoutes.store.url());
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Service' : 'Create Service'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground md:text-3xl">
                    {{ isEditing ? 'Edit Service' : 'Create Service' }}
                </h1>
            </div>
            <Link
                :href="servicesRoutes.index.url()"
                class="text-sm font-medium text-muted-foreground hover:text-foreground"
            >
                &larr; Back to List
            </Link>
        </div>

        <div class="rounded-xl border border-border bg-card p-6 md:p-8 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Service Name
                            <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 sm:text-sm"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Service Category
                            <span class="text-destructive">*</span></label
                        >
                        <select
                            v-model="form.category"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 sm:text-sm"
                        >
                            <option value="standard">Standard Business SaaS (Flat Rate / Month)</option>
                            <option value="education">🎓 School / Educational System (Student-Based Tiers & MoMo Collections)</option>
                        </select>
                        <p
                            v-if="form.errors.category"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.category }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Description</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 sm:text-sm"
                        ></textarea>
                        <p
                            v-if="form.errors.description"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>

                <div v-if="!isEditing" class="rounded-xl border border-primary/20 bg-primary/10 p-4">
                    <p class="text-sm text-primary">
                        <strong>Tip:</strong> After saving this service, you'll be taken to the service detail page where you can add <strong>Modules</strong> (features) and bundle them into <strong>Packages</strong>.
                    </p>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="buttonVariants({ variant: 'default' })"
                        class="disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Service' : 'Save Service' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
