<script setup lang="ts">
import servicesRoutes from '@/routes/services';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Info } from '@lucide/vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { selectClass, textareaClass } from '@/lib/ui';

const props = defineProps<{
    service?: any;
}>();

const isEditing = !!props.service;

const form = useForm({
    name: props.service?.name || '',
    category: props.service?.category || 'standard',
    description: props.service?.description || '',
});

const backHref = isEditing
    ? servicesRoutes.show.url(props.service.id)
    : servicesRoutes.index.url();

const submit = () => {
    if (isEditing) {
        form.put(servicesRoutes.update.url(props.service.id));
    } else {
        form.post(servicesRoutes.store.url());
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit service' : 'Add service'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="isEditing ? 'Edit service' : 'Add service'"
            :description="
                isEditing
                    ? 'Update the name, category or description.'
                    : 'A product you sell. Modules and packages are added next.'
            "
            :back-href="backHref"
            :back-label="isEditing ? 'Back to service' : 'Back to services'"
        />

        <form
            @submit.prevent="submit"
            class="border-border bg-card overflow-hidden rounded-xl border shadow-xs"
        >
            <div class="space-y-5 p-6">
                <FormField
                    label="Service name"
                    for="name"
                    required
                    :error="form.errors.name"
                >
                    <Input id="name" v-model="form.name" required autofocus />
                </FormField>

                <FormField
                    label="Category"
                    for="category"
                    required
                    :error="form.errors.category"
                    hint="Education services are priced per student and can be paid by students via MoMo."
                >
                    <select
                        id="category"
                        v-model="form.category"
                        :class="selectClass"
                    >
                        <option value="standard">
                            Standard: flat monthly price
                        </option>
                        <option value="education">
                            Education: priced per student, MoMo collections
                        </option>
                    </select>
                </FormField>

                <FormField
                    label="Description"
                    for="description"
                    :error="form.errors.description"
                >
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        :class="textareaClass"
                    ></textarea>
                </FormField>

                <div
                    v-if="!isEditing"
                    class="border-info/20 bg-info/5 text-foreground flex gap-3 rounded-lg border p-4 text-sm"
                >
                    <Info class="text-info mt-0.5 size-4 shrink-0" />
                    <p>
                        After saving you'll go to the service page, where you
                        can add <strong>modules</strong> (features) and bundle
                        them into <strong>packages</strong>.
                    </p>
                </div>
            </div>

            <div
                class="border-border bg-muted/30 flex justify-end gap-2 border-t px-6 py-4"
            >
                <Link
                    :href="backHref"
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    Cancel
                </Link>
                <Button type="submit" :disabled="form.processing">
                    {{ isEditing ? 'Save changes' : 'Add service' }}
                </Button>
            </div>
        </form>
    </div>
</template>
