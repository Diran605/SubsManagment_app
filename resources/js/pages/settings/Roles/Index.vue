<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    roles: Array<{ id: number, name: string, permissions: Array<{ id: number, name: string }> }>;
    permissions: Array<{ id: number, name: string }>;
}>();

const form = useForm({
    name: '',
    permissions: [] as number[],
});

const resources = [
    { name: 'Businesses', slug: 'businesses', actions: ['view', 'create', 'edit', 'delete'] },
    { name: 'Services', slug: 'services', actions: ['view', 'create', 'edit', 'delete'] },
    { name: 'Packages', slug: 'packages', actions: ['create', 'edit', 'delete'] },
    { name: 'Modules', slug: 'modules', actions: ['create', 'edit', 'delete'] },
    { name: 'Subscriptions', slug: 'subscriptions', actions: ['view', 'create', 'edit', 'delete', 'restore'] },
    { name: 'Reports', slug: 'reports', actions: ['view'] },
    { name: 'Staff Accounts', slug: 'staff', actions: ['manage'] },
    { name: 'Roles', slug: 'roles', actions: ['manage'] },
    { name: 'Settings', slug: 'settings', actions: ['manage'] },
];

const getPermissionId = (action: string, slug: string) => {
    const permName = `${action} ${slug}`;
    const p = props.permissions.find(p => p.name === permName);
    return p ? p.id : null;
};

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
                    <Label class="mb-2 block">Permissions Matrix</Label>
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="border-b border-border bg-orange-500 text-white">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Resource</th>
                                    <th class="px-4 py-3 font-medium text-center">View</th>
                                    <th class="px-4 py-3 font-medium text-center">Create</th>
                                    <th class="px-4 py-3 font-medium text-center">Edit</th>
                                    <th class="px-4 py-3 font-medium text-center">Delete</th>
                                    <th class="px-4 py-3 font-medium text-center">Restore</th>
                                    <th class="px-4 py-3 font-medium text-center">Manage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="res in resources" :key="res.slug">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ res.name }}</td>
                                    
                                    <!-- Dynamic Checkboxes -->
                                    <td v-for="action in ['view', 'create', 'edit', 'delete', 'restore', 'manage']" :key="action" class="px-4 py-3 text-center">
                                        <template v-if="res.actions.includes(action) && getPermissionId(action, res.slug)">
                                            <Checkbox
                                                :checked="form.permissions.includes(getPermissionId(action, res.slug) as number)"
                                                @update:checked="(checked) => {
                                                    const pid = getPermissionId(action, res.slug);
                                                    if (!pid) return;
                                                    if (checked) form.permissions.push(pid);
                                                    else form.permissions = form.permissions.filter(p => p !== pid);
                                                }"
                                            />
                                        </template>
                                        <span v-else class="text-gray-300">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
