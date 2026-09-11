<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { ref } from 'vue';

const props = defineProps<{
    roles: Array<{ id: number, name: string, permissions: Array<{ id: number, name: string }> }>;
    permissions: Array<{ id: number, name: string }>;
}>();

const form = useForm({
    name: '',
    permissions: [] as number[],
});

const editingRole = ref<number | null>(null);

const startEdit = (role: any) => {
    editingRole.value = role.id;
    form.name = role.name;
    form.permissions = role.permissions.map((p: any) => p.id);
};

const cancelEdit = () => {
    editingRole.value = null;
    form.reset();
};

const submit = () => {
    if (editingRole.value) {
        form.put(route('roles.update', editingRole.value), {
            onSuccess: () => cancelEdit(),
        });
    } else {
        form.post(route('roles.store'), {
            onSuccess: () => form.reset(),
        });
    }
};

const deleteRole = (id: number) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('roles.destroy', id));
    }
};
</script>

<template>
    <Head title="Roles & Permissions" />

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Roles & Permissions"
            description="Manage roles and their permissions."
        />

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-medium mb-4">{{ editingRole ? 'Edit Role' : 'Create New Role' }}</h3>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label for="name">Role Name</Label>
                    <Input id="name" v-model="form.name" required class="mt-1 block w-full" />
                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                </div>
                
                <div>
                    <Label class="mb-2 block">Permissions</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <label v-for="permission in permissions" :key="permission.id" class="flex items-center space-x-2">
                            <Checkbox 
                                :checked="form.permissions.includes(permission.id)"
                                @update:checked="(checked) => {
                                    if (checked) form.permissions.push(permission.id);
                                    else form.permissions = form.permissions.filter(p => p !== permission.id);
                                }"
                            />
                            <span class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">{{ permission.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <Button type="submit" :disabled="form.processing">
                        {{ editingRole ? 'Update Role' : 'Create Role' }}
                    </Button>
                    <Button v-if="editingRole" type="button" variant="outline" @click="cancelEdit">
                        Cancel
                    </Button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white shadow-sm mt-8">
            <div class="border-b border-gray-100 p-4">
                <h3 class="font-semibold text-gray-900">Existing Roles</h3>
            </div>
            <div class="p-4">
                <ul class="space-y-4">
                    <li v-for="role in roles" :key="role.id" class="flex flex-col space-y-2 border-b pb-4 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">{{ role.name }}</span>
                            <div class="space-x-2">
                                <Button size="sm" variant="outline" @click="startEdit(role)">Edit</Button>
                                <Button size="sm" variant="destructive" @click="deleteRole(role.id)" v-if="role.name !== 'Admin'">Delete</Button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            Permissions: {{ role.permissions.map(p => p.name).join(', ') || 'None' }}
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
