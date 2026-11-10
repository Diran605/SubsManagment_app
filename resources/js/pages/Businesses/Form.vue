<script setup lang="ts">
import businessesRoutes from '@/routes/businesses';
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
        form.put(businessesRoutes.update.url(props.business!.id));
    } else {
        form.post(businessesRoutes.store.url());
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Business' : 'Create Business'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1
                    class="text-foreground text-2xl font-semibold tracking-tight md:text-3xl"
                >
                    {{ isEditing ? 'Edit Business' : 'Create Business' }}
                </h1>
            </div>
            <Link
                :href="businessesRoutes.index.url()"
                class="text-muted-foreground hover:text-foreground text-sm font-medium"
            >
                &larr; Back to List
            </Link>
        </div>

        <div
            class="border-border bg-card rounded-xl border p-6 shadow-sm md:p-8"
        >
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label
                        class="text-foreground mb-1 block text-sm font-medium"
                        >Business Name
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

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Contact Person</label
                        >
                        <input
                            v-model="form.contact_person"
                            type="text"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        />
                        <p
                            v-if="form.errors.contact_person"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.contact_person }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Phone</label
                        >
                        <input
                            v-model="form.phone"
                            type="text"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>
                </div>

                <div>
                    <label
                        class="text-foreground mb-1 block text-sm font-medium"
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                    />
                    <p
                        v-if="form.errors.email"
                        class="text-destructive mt-1 text-sm"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label
                        class="text-foreground mb-1 block text-sm font-medium"
                        >Address</label
                    >
                    <textarea
                        v-model="form.address"
                        rows="3"
                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                    ></textarea>
                    <p
                        v-if="form.errors.address"
                        class="text-destructive mt-1 text-sm"
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
