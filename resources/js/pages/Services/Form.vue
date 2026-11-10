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
                <h1
                    class="text-foreground text-2xl font-semibold tracking-tight md:text-3xl"
                >
                    {{ isEditing ? 'Edit Service' : 'Create Service' }}
                </h1>
            </div>
            <Link
                :href="servicesRoutes.index.url()"
                class="text-muted-foreground hover:text-foreground text-sm font-medium"
            >
                &larr; Back to List
            </Link>
        </div>

        <div
            class="border-border bg-card rounded-xl border p-6 shadow-sm md:p-8"
        >
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Service Name
                            <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Service Category
                            <span class="text-destructive">*</span></label
                        >
                        <select
                            v-model="form.category"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        >
                            <option value="standard">
                                Standard Business SaaS (Flat Rate / Month)
                            </option>
                            <option value="education">
                                🎓 School / Educational System (Student-Based
                                Tiers & MoMo Collections)
                            </option>
                        </select>
                        <p
                            v-if="form.errors.category"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.category }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Description</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        ></textarea>
                        <p
                            v-if="form.errors.description"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="!isEditing"
                    class="border-primary/20 bg-primary/10 rounded-xl border p-4"
                >
                    <p class="text-primary text-sm">
                        <strong>Tip:</strong> After saving this service, you'll
                        be taken to the service detail page where you can add
                        <strong>Modules</strong> (features) and bundle them into
                        <strong>Packages</strong>.
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
