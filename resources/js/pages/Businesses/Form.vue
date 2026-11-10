<script setup lang="ts">
import businessesRoutes from '@/routes/businesses';
import { Head, useForm } from '@inertiajs/vue3';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Link } from '@inertiajs/vue3';
import { buttonVariants } from '@/components/ui/button';
import { textareaClass } from '@/lib/ui';

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

const backHref = isEditing
    ? businessesRoutes.show.url(props.business!.id)
    : businessesRoutes.index.url();

const submit = () => {
    if (isEditing) {
        form.put(businessesRoutes.update.url(props.business!.id));
    } else {
        form.post(businessesRoutes.store.url());
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit business' : 'Add business'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="isEditing ? 'Edit business' : 'Add business'"
            :description="
                isEditing
                    ? 'Update the client\'s contact details.'
                    : 'Add a client so you can record subscriptions for them.'
            "
            :back-href="backHref"
            :back-label="isEditing ? 'Back to business' : 'Back to businesses'"
        />

        <form
            @submit.prevent="submit"
            class="border-border bg-card overflow-hidden rounded-xl border shadow-xs"
        >
            <div class="space-y-5 p-6">
                <FormField
                    label="Business name"
                    for="name"
                    required
                    :error="form.errors.name"
                >
                    <Input id="name" v-model="form.name" required autofocus />
                </FormField>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <FormField
                        label="Contact person"
                        for="contact_person"
                        :error="form.errors.contact_person"
                    >
                        <Input
                            id="contact_person"
                            v-model="form.contact_person"
                        />
                    </FormField>
                    <FormField
                        label="Phone"
                        for="phone"
                        :error="form.errors.phone"
                    >
                        <Input id="phone" v-model="form.phone" type="tel" />
                    </FormField>
                </div>

                <FormField label="Email" for="email" :error="form.errors.email">
                    <Input id="email" v-model="form.email" type="email" />
                </FormField>

                <FormField
                    label="Address"
                    for="address"
                    :error="form.errors.address"
                >
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        :class="textareaClass"
                    ></textarea>
                </FormField>
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
                    {{ isEditing ? 'Save changes' : 'Add business' }}
                </Button>
            </div>
        </form>
    </div>
</template>
