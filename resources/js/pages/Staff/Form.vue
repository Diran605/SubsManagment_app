<script setup lang="ts">
import staffRoutes from '@/routes/staff';
import { Head, Link, useForm } from '@inertiajs/vue3';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

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
    roles: props.staff?.roles
        ? props.staff.roles.map((r: any) => r.name)
        : ([] as string[]),
});

const backHref = isEditing
    ? staffRoutes.show.url(props.staff.id)
    : staffRoutes.index.url();

const submit = () => {
    if (isEditing) {
        form.put(staffRoutes.update.url(props.staff.id));
    } else {
        form.post(staffRoutes.store.url());
    }
};

const checkboxClass =
    'border-input text-primary focus-visible:ring-ring/50 size-4 rounded border focus-visible:ring-2';
</script>

<template>
    <Head :title="isEditing ? 'Edit staff' : 'Add staff'" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="isEditing ? 'Edit staff' : 'Add staff'"
            :description="
                isEditing
                    ? 'Update account details, access and roles.'
                    : 'Create a login for a member of your team.'
            "
            :back-href="backHref"
            :back-label="isEditing ? 'Back to staff member' : 'Back to staff'"
        />

        <form
            @submit.prevent="submit"
            class="border-border bg-card overflow-hidden rounded-xl border shadow-xs"
        >
            <div class="space-y-5 p-6">
                <h2 class="text-foreground text-sm font-semibold">Account</h2>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <FormField
                        label="Name"
                        for="name"
                        required
                        :error="form.errors.name"
                    >
                        <Input
                            id="name"
                            v-model="form.name"
                            required
                            autofocus
                        />
                    </FormField>
                    <FormField
                        label="Email"
                        for="email"
                        required
                        :error="form.errors.email"
                    >
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                        />
                    </FormField>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <FormField
                        label="Password"
                        for="password"
                        :required="!isEditing"
                        :error="form.errors.password"
                        :hint="
                            isEditing
                                ? 'Leave blank to keep the current password.'
                                : undefined
                        "
                    >
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            :required="!isEditing"
                        />
                    </FormField>
                    <FormField
                        label="Confirm password"
                        for="password_confirmation"
                        :required="!isEditing"
                    >
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            :required="!isEditing"
                        />
                    </FormField>
                </div>

                <label
                    for="is_active"
                    class="border-border hover:bg-muted/40 flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-colors"
                >
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        :class="[checkboxClass, 'mt-0.5']"
                    />
                    <span>
                        <span class="text-foreground block text-sm font-medium">
                            Account is active
                        </span>
                        <span class="text-muted-foreground block text-xs">
                            Inactive staff can't log in.
                        </span>
                    </span>
                </label>
                <p
                    v-if="form.errors.is_active"
                    class="text-destructive text-sm"
                >
                    {{ form.errors.is_active }}
                </p>
            </div>

            <div
                v-if="roles && roles.length > 0"
                class="border-border space-y-3 border-t p-6"
            >
                <div>
                    <h2 class="text-foreground text-sm font-semibold">Roles</h2>
                    <p class="text-muted-foreground text-xs">
                        Roles decide what this person can see and change.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <label
                        v-for="role in roles"
                        :key="role.id"
                        class="border-border hover:bg-muted/40 has-checked:border-primary/40 has-checked:bg-primary/5 flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors"
                    >
                        <input
                            type="checkbox"
                            :value="role.name"
                            v-model="form.roles"
                            :class="checkboxClass"
                        />
                        <span class="text-foreground text-sm font-medium">{{
                            role.name
                        }}</span>
                    </label>
                </div>
                <p v-if="form.errors.roles" class="text-destructive text-sm">
                    {{ form.errors.roles }}
                </p>
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
                    {{ isEditing ? 'Save changes' : 'Add staff' }}
                </Button>
            </div>
        </form>
    </div>
</template>
