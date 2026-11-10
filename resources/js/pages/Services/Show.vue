<script setup lang="ts">
import { table } from '@/lib/ui';
import servicesRoutes from '@/routes/services';
import modulesRoutes from '@/routes/modules';
import packagesRoutes from '@/routes/packages';
import businessesRoutes from '@/routes/businesses';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const page = usePage<any>();
const canEdit = computed(() =>
    page.props.auth?.user?.permissions?.includes('edit services'),
);
const canDelete = computed(() =>
    page.props.auth?.user?.permissions?.includes('delete services'),
);
const canCreatePackages = computed(() =>
    page.props.auth?.user?.permissions?.includes('create packages'),
);
const canEditPackages = computed(() =>
    page.props.auth?.user?.permissions?.includes('edit packages'),
);
const canDeletePackages = computed(() =>
    page.props.auth?.user?.permissions?.includes('delete packages'),
);
const canCreateModules = computed(() =>
    page.props.auth?.user?.permissions?.includes('create modules'),
);
const canEditModules = computed(() =>
    page.props.auth?.user?.permissions?.includes('edit modules'),
);
const canDeleteModules = computed(() =>
    page.props.auth?.user?.permissions?.includes('delete modules'),
);

const props = defineProps<{
    service: any;
}>();

const deleteForm = useForm({});

// The server reports "in use" problems under these keys (see ModuleController/PackageController).
type DeleteErrors = { module?: string; package?: string };

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this service?')) {
        deleteForm.delete(servicesRoutes.destroy.url(props.service.id));
    }
};

const moduleForm = useForm({
    name: '',
    description: '',
});

const submitModule = () => {
    moduleForm.post(servicesRoutes.modules.store.url(props.service.id), {
        preserveScroll: true,
        onSuccess: () => moduleForm.reset(),
    });
};

const packageForm = useForm({
    name: '',
    description: '',
    price: '',
    pricing_model:
        props.service?.category === 'education' ? 'per_student' : 'flat',
    payer_type: 'school',
    supported_modes: [] as string[],
    modules: [],
});

const submitPackage = () => {
    packageForm.post(servicesRoutes.packages.store.url(props.service.id), {
        preserveScroll: true,
        onSuccess: () => packageForm.reset(),
    });
};

const deleteModuleForm = useForm({});
const deleteModule = (id: number) => {
    if (confirm('Are you sure you want to delete this module?')) {
        deleteModuleForm.delete(modulesRoutes.destroy.url(id), {
            preserveScroll: true,
        });
    }
};

// ── Module inline editing ──────────────────────────────────────────────
const editingModuleId = ref<number | null>(null);
const editModuleForm = useForm({ name: '', description: '' });

const startEditModule = (mod: any) => {
    editingModuleId.value = mod.id;
    editModuleForm.name = mod.name;
    editModuleForm.description = mod.description || '';
};

const cancelEditModule = () => {
    editingModuleId.value = null;
    editModuleForm.reset();
};

const submitEditModule = (id: number) => {
    editModuleForm.put(modulesRoutes.update.url(id), {
        preserveScroll: true,
        onSuccess: () => {
            editingModuleId.value = null;
        },
    });
};

const deletePackageForm = useForm({});
const deletePackage = (id: number) => {
    if (confirm('Are you sure you want to delete this package?')) {
        deletePackageForm.delete(packagesRoutes.destroy.url(id), {
            preserveScroll: true,
        });
    }
};

// ── Package inline editing ─────────────────────────────────────────────
const editingPackageId = ref<number | null>(null);
const editPackageForm = useForm({
    name: '',
    description: '',
    price: '',
    pricing_model: 'flat',
    payer_type: 'school',
    supported_modes: [] as string[],
    modules: [] as number[],
});

const startEditPackage = (pkg: any) => {
    editingPackageId.value = pkg.id;
    editPackageForm.name = pkg.name;
    editPackageForm.description = pkg.description || '';
    editPackageForm.price = pkg.price ?? '';
    editPackageForm.pricing_model = pkg.pricing_model || 'flat';
    editPackageForm.payer_type = pkg.payer_type || 'school';
    editPackageForm.supported_modes = pkg.supported_modes ?? [];
    editPackageForm.modules = pkg.modules?.map((m: any) => m.id) ?? [];
};

const cancelEditPackage = () => {
    editingPackageId.value = null;
    editPackageForm.reset();
};

const submitEditPackage = (id: number) => {
    editPackageForm.put(packagesRoutes.update.url(id), {
        preserveScroll: true,
        onSuccess: () => {
            editingPackageId.value = null;
        },
    });
};
</script>

<template>
    <Head :title="service.name" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1
                    class="text-foreground text-2xl font-semibold tracking-tight md:text-3xl"
                >
                    {{ service.name }}
                </h1>
                <p class="text-muted-foreground mt-1 text-sm">
                    Service Details & Active Subscriptions
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="servicesRoutes.index.url()"
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    Back to List
                </Link>
                <Link
                    v-if="canEdit"
                    :href="servicesRoutes.edit.url(service.id)"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    Edit
                </Link>
                <button
                    v-if="canDelete"
                    @click="confirmDelete"
                    :class="buttonVariants({ variant: 'destructive' })"
                >
                    Delete
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Service Info -->
            <div
                class="border-border bg-card col-span-1 h-fit space-y-6 rounded-xl border p-6 shadow-sm"
            >
                <div>
                    <h3 class="text-foreground mb-2 font-semibold">
                        Description
                    </h3>
                    <p class="text-muted-foreground text-sm">
                        {{ service.description || 'No description provided.' }}
                    </p>
                </div>

                <div class="border-border mt-4 border-t pt-4">
                    <dt class="text-muted-foreground text-xs">Added By</dt>
                    <dd class="text-foreground mt-1 text-xs font-medium">
                        {{ service.creator?.name || 'Unknown' }}
                    </dd>
                </div>
            </div>

            <!-- Subscriptions using this Service -->
            <div
                class="border-border bg-card col-span-2 flex flex-col rounded-xl border shadow-xs"
            >
                <div
                    class="border-border flex items-center justify-between border-b p-4"
                >
                    <h3 class="text-foreground font-semibold">
                        Subscribed Businesses
                    </h3>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-border bg-muted/50 text-muted-foreground border-b"
                        >
                            <tr>
                                <th class="px-4 py-3 font-medium">Business</th>
                                <th class="px-4 py-3 font-medium">Mode</th>
                                <th class="px-4 py-3 font-medium">End Date</th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-border divide-y">
                            <tr
                                v-for="sub in service.subscriptions"
                                :key="sub.id"
                                :class="table.row"
                            >
                                <td
                                    class="text-foreground px-4 py-3 font-medium"
                                >
                                    <Link
                                        :href="
                                            businessesRoutes.show.url(
                                                sub.business_id,
                                            )
                                        "
                                        class="text-primary hover:underline"
                                        >{{ sub.business?.name }}</Link
                                    >
                                </td>
                                <td
                                    class="text-muted-foreground px-4 py-3 capitalize"
                                >
                                    {{ sub.mode }}
                                </td>
                                <td class="text-muted-foreground px-4 py-3">
                                    {{ sub.end_date }}
                                </td>
                                <td
                                    class="text-foreground px-4 py-3 text-right font-medium"
                                >
                                    {{
                                        Number(sub.amount).toLocaleString(
                                            'en-US',
                                        )
                                    }}
                                    FCFA
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !service.subscriptions ||
                                    service.subscriptions.length === 0
                                "
                            >
                                <td colspan="4" :class="table.empty">
                                    No active subscriptions for this service.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modules & Packages Configuration (Full Width) -->
            <div
                class="col-span-1 grid grid-cols-1 gap-6 md:col-span-3 md:grid-cols-2"
            >
                <!-- Modules -->
                <div
                    class="border-border bg-card flex flex-col rounded-xl border shadow-xs"
                >
                    <div
                        class="border-border flex items-center justify-between border-b p-4"
                    >
                        <h3 class="text-foreground font-semibold">
                            Service Modules
                        </h3>
                    </div>
                    <div class="flex-1 p-4">
                        <ul class="space-y-3">
                            <li
                                v-for="mod in service.modules"
                                :key="mod.id"
                                class="border-border border-b pb-3 last:border-0 last:pb-0"
                            >
                                <!-- View mode -->
                                <div
                                    v-if="editingModuleId !== mod.id"
                                    class="group flex items-center justify-between"
                                >
                                    <div>
                                        <div
                                            class="text-foreground text-sm font-medium"
                                        >
                                            {{ mod.name }}
                                        </div>
                                        <div
                                            class="text-muted-foreground text-xs"
                                        >
                                            {{
                                                mod.description ||
                                                'No description'
                                            }}
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <button
                                            v-if="canEditModules"
                                            @click="startEditModule(mod)"
                                            class="border-primary/30 bg-primary/10 text-primary hover:bg-primary inline-flex items-center rounded border px-2 py-1 text-xs font-medium transition-colors hover:text-white"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="canDeleteModules"
                                            @click="deleteModule(mod.id)"
                                            class="border-destructive/30 bg-destructive/10 text-destructive hover:bg-destructive inline-flex items-center rounded border px-2 py-1 text-xs font-medium transition-colors hover:text-white"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                <!-- Edit mode -->
                                <form
                                    v-else
                                    @submit.prevent="submitEditModule(mod.id)"
                                    class="bg-muted/30 space-y-2 rounded-lg p-3"
                                >
                                    <div class="flex gap-2">
                                        <input
                                            v-model="editModuleForm.name"
                                            type="text"
                                            required
                                            placeholder="Module name"
                                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex-1 rounded-md px-3 py-1.5 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                        />
                                    </div>
                                    <input
                                        v-model="editModuleForm.description"
                                        type="text"
                                        placeholder="Description (optional)"
                                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-1.5 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    />
                                    <p
                                        v-if="editModuleForm.errors.name"
                                        class="text-destructive text-xs"
                                    >
                                        {{ editModuleForm.errors.name }}
                                    </p>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="cancelEditModule"
                                            class="border-border text-muted-foreground hover:bg-muted inline-flex items-center rounded border px-3 py-1 text-xs font-medium transition-colors"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="
                                                editModuleForm.processing
                                            "
                                            class="border-primary bg-primary hover:bg-primary/90 inline-flex items-center rounded border px-3 py-1 text-xs font-medium text-white transition-colors"
                                        >
                                            Save
                                        </button>
                                    </div>
                                </form>
                            </li>
                            <li
                                v-if="
                                    !service.modules ||
                                    service.modules.length === 0
                                "
                                class="text-muted-foreground py-4 text-center text-sm"
                            >
                                No modules configured.
                            </li>
                        </ul>
                    </div>
                    <div
                        v-if="canCreateModules"
                        class="border-border bg-muted/30 rounded-b-2xl border-t p-4"
                    >
                        <form @submit.prevent="submitModule" class="space-y-3">
                            <h4 class="text-foreground text-sm font-medium">
                                Add New Module
                            </h4>
                            <div>
                                <input
                                    v-model="moduleForm.name"
                                    type="text"
                                    placeholder="Module Name"
                                    required
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                                <p
                                    v-if="moduleForm.errors.name"
                                    class="text-destructive mt-1 text-xs"
                                >
                                    {{ moduleForm.errors.name }}
                                </p>
                            </div>
                            <div>
                                <input
                                    v-model="moduleForm.description"
                                    type="text"
                                    placeholder="Description (Optional)"
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>
                            <div
                                v-if="deleteModuleForm.hasErrors"
                                class="text-destructive text-xs"
                            >
                                {{
                                    (deleteModuleForm.errors as DeleteErrors)
                                        .module
                                }}
                            </div>
                            <button
                                type="submit"
                                :disabled="moduleForm.processing"
                                :class="
                                    buttonVariants({
                                        variant: 'secondary',
                                        class: 'w-full',
                                    })
                                "
                            >
                                Add module
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Packages -->
                <div
                    class="border-border bg-card flex flex-col rounded-xl border shadow-xs"
                >
                    <div
                        class="border-border flex items-center justify-between border-b p-4"
                    >
                        <h3 class="text-foreground font-semibold">
                            Subscription Packages
                        </h3>
                    </div>
                    <div class="flex-1 p-4">
                        <ul class="space-y-4">
                            <li
                                v-for="pkg in service.packages"
                                :key="pkg.id"
                                class="border-border bg-background/50 rounded-lg border p-3"
                            >
                                <!-- View mode -->
                                <div
                                    v-if="editingPackageId !== pkg.id"
                                    class="group relative"
                                >
                                    <div
                                        class="mb-3 flex items-start justify-between pr-2"
                                    >
                                        <div>
                                            <div
                                                class="text-foreground text-sm font-medium"
                                            >
                                                {{ pkg.name }}
                                            </div>
                                            <div
                                                class="text-muted-foreground mt-0.5 text-xs"
                                            >
                                                {{
                                                    pkg.description ||
                                                    'No description'
                                                }}
                                            </div>
                                        </div>
                                        <div
                                            class="mt-1 flex flex-col items-end gap-1"
                                        >
                                            <span
                                                class="inline-flex items-center rounded-md bg-orange-500/10 px-2.5 py-1 text-xs font-semibold text-orange-600 ring-1 ring-orange-500/20 ring-inset dark:text-orange-400"
                                            >
                                                {{
                                                    pkg.price
                                                        ? Number(
                                                              pkg.price,
                                                          ).toLocaleString(
                                                              'en-US',
                                                          ) +
                                                          (pkg.pricing_model ===
                                                          'per_student'
                                                              ? ' FCFA / student'
                                                              : ' FCFA / mo')
                                                        : 'Free'
                                                }}
                                            </span>
                                            <div
                                                class="mt-1 flex flex-wrap justify-end gap-1"
                                            >
                                                <span
                                                    v-if="
                                                        pkg.pricing_model ===
                                                        'per_student'
                                                    "
                                                    :class="
                                                        pkg.payer_type ===
                                                        'student_momo'
                                                            ? 'bg-info/10 text-info border-info/20'
                                                            : 'bg-success/10 text-success border-success/20'
                                                    "
                                                    class="inline-flex items-center rounded border px-1.5 py-0.5 text-[10px] font-medium"
                                                >
                                                    {{
                                                        pkg.payer_type ===
                                                        'student_momo'
                                                            ? '📱 Student MoMo'
                                                            : '🏫 School Pays'
                                                    }}
                                                </span>
                                                <span
                                                    v-for="mode in pkg.supported_modes"
                                                    :key="mode"
                                                    class="bg-muted text-muted-foreground border-border inline-flex items-center rounded border px-1.5 py-0.5 text-[10px] font-medium uppercase"
                                                >
                                                    {{ mode }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <span
                                            class="text-muted-foreground mb-1.5 block text-[11px] font-medium tracking-wider uppercase"
                                            >Includes</span
                                        >
                                        <div class="flex flex-wrap gap-1.5">
                                            <span
                                                v-for="mod in pkg.modules"
                                                :key="mod.id"
                                                class="bg-muted text-muted-foreground border-border inline-flex items-center rounded-md border px-2 py-1 text-[11px] font-medium"
                                            >
                                                {{ mod.name }}
                                            </span>
                                            <span
                                                v-if="
                                                    !pkg.modules ||
                                                    pkg.modules.length === 0
                                                "
                                                class="text-muted-foreground/60 text-xs italic"
                                                >None</span
                                            >
                                        </div>
                                    </div>
                                    <div
                                        class="flex gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <button
                                            v-if="canEditPackages"
                                            @click="startEditPackage(pkg)"
                                            class="border-primary/30 bg-primary/10 text-primary hover:bg-primary inline-flex items-center rounded border px-2 py-1 text-xs font-medium transition-colors hover:text-white"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="canDeletePackages"
                                            @click="deletePackage(pkg.id)"
                                            class="border-destructive/30 bg-destructive/10 text-destructive hover:bg-destructive inline-flex items-center rounded border px-2 py-1 text-xs font-medium transition-colors hover:text-white"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                <!-- Edit mode -->
                                <form
                                    v-else
                                    @submit.prevent="submitEditPackage(pkg.id)"
                                    class="space-y-3"
                                >
                                    <h4
                                        class="text-foreground text-sm font-medium"
                                    >
                                        Edit Package
                                    </h4>
                                    <div>
                                        <input
                                            v-model="editPackageForm.name"
                                            type="text"
                                            required
                                            placeholder="Package Name"
                                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                        />
                                        <p
                                            v-if="editPackageForm.errors.name"
                                            class="text-destructive mt-1 text-xs"
                                        >
                                            {{ editPackageForm.errors.name }}
                                        </p>
                                    </div>
                                    <input
                                        v-model="editPackageForm.description"
                                        type="text"
                                        placeholder="Description (Optional)"
                                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    />

                                    <div
                                        class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                class="text-foreground mb-1 block text-xs font-medium"
                                                >Pricing Model</label
                                            >
                                            <select
                                                v-model="
                                                    editPackageForm.pricing_model
                                                "
                                                class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                            >
                                                <option value="flat">
                                                    Flat Monthly Fee
                                                </option>
                                                <option value="per_student">
                                                    Per-Student Annual Fee
                                                </option>
                                            </select>
                                        </div>
                                        <div
                                            v-if="
                                                editPackageForm.pricing_model ===
                                                'per_student'
                                            "
                                        >
                                            <label
                                                class="text-foreground mb-1 block text-xs font-medium"
                                                >Payer</label
                                            >
                                            <select
                                                v-model="
                                                    editPackageForm.payer_type
                                                "
                                                class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                            >
                                                <option value="school">
                                                    School (Institutional
                                                    Invoice)
                                                </option>
                                                <option value="student_momo">
                                                    Students (Mobile Money)
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="text-foreground mb-1 block text-xs font-medium"
                                        >
                                            {{
                                                editPackageForm.pricing_model ===
                                                'per_student'
                                                    ? 'Rate per Student (FCFA)'
                                                    : 'Monthly Price (FCFA)'
                                            }}
                                        </label>
                                        <input
                                            v-model="editPackageForm.price"
                                            type="number"
                                            step="0.01"
                                            required
                                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="text-foreground mt-2 mb-1.5 block text-xs font-medium"
                                            >Supported Modes</label
                                        >
                                        <div class="flex flex-wrap gap-3">
                                            <label
                                                class="flex cursor-pointer items-center space-x-2 text-sm"
                                            >
                                                <input
                                                    type="checkbox"
                                                    value="online"
                                                    v-model="
                                                        editPackageForm.supported_modes
                                                    "
                                                    class="border-input bg-background text-primary rounded"
                                                />
                                                <span class="text-foreground"
                                                    >Online</span
                                                >
                                            </label>
                                            <label
                                                class="flex cursor-pointer items-center space-x-2 text-sm"
                                            >
                                                <input
                                                    type="checkbox"
                                                    value="offline"
                                                    v-model="
                                                        editPackageForm.supported_modes
                                                    "
                                                    class="border-input bg-background text-primary rounded"
                                                />
                                                <span class="text-foreground"
                                                    >Offline</span
                                                >
                                            </label>
                                            <label
                                                class="flex cursor-pointer items-center space-x-2 text-sm"
                                            >
                                                <input
                                                    type="checkbox"
                                                    value="hybrid"
                                                    v-model="
                                                        editPackageForm.supported_modes
                                                    "
                                                    class="border-input bg-background text-primary rounded"
                                                />
                                                <span class="text-foreground"
                                                    >Hybrid</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                    <div
                                        v-if="
                                            service.modules &&
                                            service.modules.length > 0
                                        "
                                    >
                                        <label
                                            class="text-foreground mb-1.5 block text-xs font-medium"
                                            >Modules</label
                                        >
                                        <div
                                            class="border-border bg-background max-h-32 space-y-1 overflow-y-auto rounded-md border p-2"
                                        >
                                            <label
                                                v-for="mod in service.modules"
                                                :key="mod.id"
                                                class="hover:bg-muted flex cursor-pointer items-center space-x-2 rounded p-1.5 text-sm transition-colors"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :value="mod.id"
                                                    v-model="
                                                        editPackageForm.modules
                                                    "
                                                    class="border-input bg-background text-primary rounded"
                                                />
                                                <span class="text-foreground">{{
                                                    mod.name
                                                }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="cancelEditPackage"
                                            class="border-border text-muted-foreground hover:bg-muted inline-flex items-center rounded border px-3 py-1.5 text-xs font-medium transition-colors"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="
                                                editPackageForm.processing
                                            "
                                            class="border-primary bg-primary hover:bg-primary/90 inline-flex items-center rounded border px-3 py-1.5 text-xs font-medium text-white transition-colors"
                                        >
                                            Save changes
                                        </button>
                                    </div>
                                </form>
                            </li>
                            <li
                                v-if="
                                    !service.packages ||
                                    service.packages.length === 0
                                "
                                class="text-muted-foreground py-4 text-center text-sm"
                            >
                                No packages configured.
                            </li>
                        </ul>
                    </div>
                    <div
                        v-if="canCreatePackages"
                        class="border-border bg-muted/30 rounded-b-2xl border-t p-4"
                    >
                        <form @submit.prevent="submitPackage" class="space-y-3">
                            <h4 class="text-foreground text-sm font-medium">
                                Create New Package
                            </h4>
                            <div
                                v-if="deletePackageForm.hasErrors"
                                class="text-destructive mb-2 text-xs"
                            >
                                {{
                                    (deletePackageForm.errors as DeleteErrors)
                                        .package
                                }}
                            </div>
                            <div>
                                <input
                                    v-model="packageForm.name"
                                    type="text"
                                    placeholder="Package Name"
                                    required
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                                <p
                                    v-if="packageForm.errors.name"
                                    class="text-destructive mt-1 text-xs"
                                >
                                    {{ packageForm.errors.name }}
                                </p>
                            </div>
                            <div>
                                <input
                                    v-model="packageForm.description"
                                    type="text"
                                    placeholder="Description (Optional)"
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="text-foreground mb-1 block text-xs font-medium"
                                        >Pricing Model</label
                                    >
                                    <select
                                        v-model="packageForm.pricing_model"
                                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    >
                                        <option value="flat">
                                            Flat Monthly Fee
                                        </option>
                                        <option value="per_student">
                                            Per-Student Annual Fee
                                        </option>
                                    </select>
                                </div>
                                <div
                                    v-if="
                                        packageForm.pricing_model ===
                                        'per_student'
                                    "
                                >
                                    <label
                                        class="text-foreground mb-1 block text-xs font-medium"
                                        >Payer</label
                                    >
                                    <select
                                        v-model="packageForm.payer_type"
                                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    >
                                        <option value="school">
                                            School (Institutional Invoice)
                                        </option>
                                        <option value="student_momo">
                                            Students (Mobile Money)
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="text-foreground mb-1 block text-xs font-medium"
                                >
                                    {{
                                        packageForm.pricing_model ===
                                        'per_student'
                                            ? 'Rate per Student (FCFA)'
                                            : 'Monthly Price (FCFA)'
                                    }}
                                </label>
                                <input
                                    v-model="packageForm.price"
                                    type="number"
                                    step="0.01"
                                    required
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-foreground mt-2 mb-1.5 block text-xs font-medium"
                                    >Supported Modes</label
                                >
                                <div class="flex flex-wrap gap-3">
                                    <label
                                        class="flex cursor-pointer items-center space-x-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            value="online"
                                            v-model="
                                                packageForm.supported_modes
                                            "
                                            class="border-input bg-background text-primary rounded"
                                        />
                                        <span class="text-foreground"
                                            >Online</span
                                        >
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center space-x-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            value="offline"
                                            v-model="
                                                packageForm.supported_modes
                                            "
                                            class="border-input bg-background text-primary rounded"
                                        />
                                        <span class="text-foreground"
                                            >Offline</span
                                        >
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center space-x-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            value="hybrid"
                                            v-model="
                                                packageForm.supported_modes
                                            "
                                            class="border-input bg-background text-primary rounded"
                                        />
                                        <span class="text-foreground"
                                            >Hybrid</span
                                        >
                                    </label>
                                </div>
                            </div>

                            <div
                                v-if="
                                    service.modules &&
                                    service.modules.length > 0
                                "
                            >
                                <label
                                    class="text-foreground mt-3 mb-1.5 block text-xs font-medium"
                                    >Select Modules for Package</label
                                >
                                <div
                                    class="border-border bg-background max-h-32 space-y-1 overflow-y-auto rounded-md border p-2"
                                >
                                    <label
                                        v-for="mod in service.modules"
                                        :key="mod.id"
                                        class="hover:bg-muted flex cursor-pointer items-center space-x-2 rounded p-1.5 text-sm transition-colors"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="mod.id"
                                            v-model="packageForm.modules"
                                            class="border-input bg-background text-primary focus:ring-primary rounded"
                                        />
                                        <span class="text-foreground">{{
                                            mod.name
                                        }}</span>
                                    </label>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="packageForm.processing"
                                :class="
                                    buttonVariants({
                                        variant: 'secondary',
                                        class: 'mt-4 w-full',
                                    })
                                "
                            >
                                Add package
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
