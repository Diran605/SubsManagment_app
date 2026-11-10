<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';
import { card, rowActionVariants, table } from '@/lib/ui';
import { formatDate, formatMoney } from '@/lib/format';
import servicesRoutes from '@/routes/services';
import modulesRoutes from '@/routes/modules';
import packagesRoutes from '@/routes/packages';
import businessesRoutes from '@/routes/businesses';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import PageHeader from '@/components/PageHeader.vue';
import { Pencil, Trash2 } from '@lucide/vue';

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

const confirmAction = useConfirm();
const confirmDelete = async () => {
    if (
        await confirmAction({
            title: 'Delete this service?',
            description:
                'It disappears from lists and can no longer be picked for new subscriptions.',
            confirmText: 'Delete service',
            tone: 'danger',
        })
    ) {
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
const deleteModule = async (id: number) => {
    if (
        await confirmAction({
            title: 'Delete this module?',
            description:
                "It is removed from this service and from any packages that include it. This can't be undone.",
            confirmText: 'Delete module',
            tone: 'danger',
        })
    ) {
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
const deletePackage = async (id: number) => {
    if (
        await confirmAction({
            title: 'Delete this package?',
            description:
                "It can no longer be picked for new subscriptions. This can't be undone.",
            confirmText: 'Delete package',
            tone: 'danger',
        })
    ) {
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

    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="service.name"
            :back-href="servicesRoutes.index.url()"
            back-label="Back to services"
        >
            <template #description>
                <span class="inline-flex flex-wrap items-center gap-2">
                    <Badge
                        :variant="
                            service.category === 'education'
                                ? 'warning'
                                : 'info'
                        "
                    >
                        {{
                            service.category === 'education'
                                ? 'Education'
                                : 'Standard'
                        }}
                    </Badge>
                    Modules, packages and the clients using this service.
                </span>
            </template>
            <template #actions>
                <button
                    v-if="canDelete"
                    type="button"
                    @click="confirmDelete"
                    :class="buttonVariants({ variant: 'outline' })"
                    class="hover:border-destructive/40 hover:bg-destructive/10 hover:text-destructive"
                >
                    <Trash2 class="size-4" /> Delete
                </button>
                <Link
                    v-if="canEdit"
                    :href="servicesRoutes.edit.url(service.id)"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    <Pencil class="size-4" /> Edit
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Service Info -->
            <section :class="[card.base, 'h-fit']">
                <div :class="card.header">
                    <h2 :class="card.title">About</h2>
                </div>
                <p :class="card.body" class="text-muted-foreground text-sm">
                    {{ service.description || 'No description yet.' }}
                </p>
                <dl
                    class="border-border bg-muted/30 divide-border divide-y border-t text-xs"
                >
                    <div class="flex justify-between gap-4 px-5 py-2.5">
                        <dt class="text-muted-foreground">Modules</dt>
                        <dd class="text-foreground font-medium">
                            {{ service.modules?.length ?? 0 }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 px-5 py-2.5">
                        <dt class="text-muted-foreground">Packages</dt>
                        <dd class="text-foreground font-medium">
                            {{ service.packages?.length ?? 0 }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 px-5 py-2.5">
                        <dt class="text-muted-foreground">Added by</dt>
                        <dd class="text-foreground">
                            {{ service.creator?.name ?? '—' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- Subscriptions using this Service -->
            <div
                class="border-border bg-card col-span-2 flex flex-col rounded-xl border shadow-xs"
            >
                <div
                    class="border-border flex items-center justify-between border-b p-4"
                >
                    <h3 :class="card.title">Clients using this service</h3>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead :class="table.head">
                            <tr>
                                <th class="px-4 py-3 font-medium">Business</th>
                                <th class="px-4 py-3 font-medium">Mode</th>
                                <th class="px-4 py-3 font-medium">Ends</th>
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
                                        class="text-foreground hover:text-primary"
                                        >{{ sub.business?.name }}</Link
                                    >
                                </td>
                                <td
                                    class="text-muted-foreground px-4 py-3 capitalize"
                                >
                                    {{ sub.mode }}
                                </td>
                                <td
                                    class="text-muted-foreground px-4 py-3 whitespace-nowrap"
                                >
                                    {{ formatDate(sub.end_date) }}
                                </td>
                                <td
                                    class="text-foreground px-4 py-3 text-right font-medium whitespace-nowrap tabular-nums"
                                >
                                    {{ formatMoney(sub.amount) }}
                                    <span
                                        class="text-muted-foreground text-xs font-normal"
                                        >FCFA</span
                                    >
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !service.subscriptions ||
                                    service.subscriptions.length === 0
                                "
                            >
                                <td colspan="4" :class="table.empty">
                                    No clients use this service right now.
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
                        <h3 :class="card.title">Modules</h3>
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
                                            :class="
                                                rowActionVariants({
                                                    tone: 'primary',
                                                })
                                            "
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="canDeleteModules"
                                            @click="deleteModule(mod.id)"
                                            :class="
                                                rowActionVariants({
                                                    tone: 'danger',
                                                })
                                            "
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
                                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex-1 rounded-md border px-3 py-1.5 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                        />
                                    </div>
                                    <input
                                        v-model="editModuleForm.description"
                                        type="text"
                                        placeholder="Description (optional)"
                                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-1.5 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
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
                                            :class="
                                                buttonVariants({
                                                    variant: 'outline',
                                                    size: 'sm',
                                                })
                                            "
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="
                                                editModuleForm.processing
                                            "
                                            :class="
                                                buttonVariants({
                                                    variant: 'default',
                                                    size: 'sm',
                                                })
                                            "
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
                        class="border-border bg-muted/30 rounded-b-xl border-t p-4"
                    >
                        <form @submit.prevent="submitModule" class="space-y-3">
                            <h4 class="text-foreground text-sm font-medium">
                                Add a module
                            </h4>
                            <div>
                                <input
                                    v-model="moduleForm.name"
                                    type="text"
                                    placeholder="Module name"
                                    required
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
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
                                    placeholder="Description (optional)"
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
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
                        <h3 :class="card.title">Packages</h3>
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
                                                class="bg-primary/10 text-primary inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold tabular-nums"
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
                                            :class="
                                                rowActionVariants({
                                                    tone: 'primary',
                                                })
                                            "
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="canDeletePackages"
                                            @click="deletePackage(pkg.id)"
                                            :class="
                                                rowActionVariants({
                                                    tone: 'danger',
                                                })
                                            "
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
                                        Edit package
                                    </h4>
                                    <div>
                                        <input
                                            v-model="editPackageForm.name"
                                            type="text"
                                            required
                                            placeholder="Package name"
                                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
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
                                        placeholder="Description (optional)"
                                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    />

                                    <div
                                        class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                class="text-foreground mb-1 block text-xs font-medium"
                                                >Pricing model</label
                                            >
                                            <select
                                                v-model="
                                                    editPackageForm.pricing_model
                                                "
                                                class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                            >
                                                <option value="flat">
                                                    Flat monthly fee
                                                </option>
                                                <option value="per_student">
                                                    Per-student annual fee
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
                                                class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                            >
                                                <option value="school">
                                                    School (institutional
                                                    invoice)
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
                                                    ? 'Price per student (FCFA)'
                                                    : 'Monthly price (FCFA)'
                                            }}
                                        </label>
                                        <input
                                            v-model="editPackageForm.price"
                                            type="number"
                                            step="0.01"
                                            required
                                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="text-foreground mt-2 mb-1.5 block text-xs font-medium"
                                            >Supported modes</label
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
                                                    class="border-input text-primary size-4 rounded border"
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
                                                    class="border-input text-primary size-4 rounded border"
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
                                                    class="border-input text-primary size-4 rounded border"
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
                                                    class="border-input text-primary size-4 rounded border"
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
                                            :class="
                                                buttonVariants({
                                                    variant: 'outline',
                                                    size: 'sm',
                                                })
                                            "
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="
                                                editPackageForm.processing
                                            "
                                            :class="
                                                buttonVariants({
                                                    variant: 'default',
                                                    size: 'sm',
                                                })
                                            "
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
                        class="border-border bg-muted/30 rounded-b-xl border-t p-4"
                    >
                        <form @submit.prevent="submitPackage" class="space-y-3">
                            <h4 class="text-foreground text-sm font-medium">
                                Add a package
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
                                    placeholder="Package name"
                                    required
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
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
                                    placeholder="Description (optional)"
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="text-foreground mb-1 block text-xs font-medium"
                                        >Pricing model</label
                                    >
                                    <select
                                        v-model="packageForm.pricing_model"
                                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    >
                                        <option value="flat">
                                            Flat monthly fee
                                        </option>
                                        <option value="per_student">
                                            Per-student annual fee
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
                                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                    >
                                        <option value="school">
                                            School (institutional invoice)
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
                                            ? 'Price per student (FCFA)'
                                            : 'Monthly price (FCFA)'
                                    }}
                                </label>
                                <input
                                    v-model="packageForm.price"
                                    type="number"
                                    step="0.01"
                                    required
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>

                            <div>
                                <label
                                    class="text-foreground mt-2 mb-1.5 block text-xs font-medium"
                                    >Supported modes</label
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
                                            class="border-input text-primary size-4 rounded border"
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
                                            class="border-input text-primary size-4 rounded border"
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
                                            class="border-input text-primary size-4 rounded border"
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
                                    >Modules in this package</label
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
                                            class="border-input text-primary size-4 rounded border"
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
