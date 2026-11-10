<script setup lang="ts">
import subscriptionsRoutes from '@/routes/subscriptions';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import { buttonVariants } from '@/components/ui/button';
import {
    addDays,
    monthsCovered,
    subscriptionEndDate,
    today,
} from '@/lib/dates';

const props = defineProps<{
    subscription?: any;
    renewing?: any;
    businesses: any[];
    services: any[];
    defaultBusinessId?: number | null;
}>();

const isEditing = !!props.subscription;
const isRenewing = !!props.renewing;
const sourceData = props.subscription || props.renewing || {};

// A renewal starts the day after the previous subscription ends, and by
// default runs for the same number of months.
const initialStartDate = isRenewing
    ? addDays(sourceData.end_date, 1)
    : sourceData.start_date || today();

const initialDuration =
    sourceData.start_date && sourceData.end_date
        ? monthsCovered(sourceData.start_date, sourceData.end_date)
        : 12;

const form = useForm({
    business_id: sourceData.business_id || props.defaultBusinessId || '',
    service_id: sourceData.service_id || '',
    package_id: sourceData.package_id || '',
    modules: sourceData.modules?.map((m: any) => m.id) || [],
    mode: sourceData.mode || '',
    amount: sourceData.amount || '',
    student_count: sourceData.student_count || '',
    start_date: initialStartDate,
    end_date: isEditing
        ? sourceData.end_date
        : subscriptionEndDate(initialStartDate, initialDuration),
    notes: isRenewing ? '' : sourceData.notes || '',
});

// Subscription length in months; the end date is derived from it.
const duration = ref(initialDuration);

const selectedService = computed(() => {
    return props.services.find((s) => s.id === form.service_id);
});

const availablePackages = computed(() => {
    return selectedService.value?.packages || [];
});

const availableModules = computed(() => {
    return selectedService.value?.modules || [];
});

const selectedPackage = computed(() => {
    return availablePackages.value.find((p: any) => p.id === form.package_id);
});

const isPerStudent = computed(() => {
    return selectedPackage.value?.pricing_model === 'per_student';
});

const isStudentMoMo = computed(() => {
    return selectedPackage.value?.payer_type === 'student_momo';
});

const availableModes = computed(() => {
    const modes = selectedPackage.value?.supported_modes;

    // A package with no modes listed accepts all of them.
    return modes && modes.length > 0 ? modes : ['online', 'offline', 'hybrid'];
});

// Recalculate the end date when the user changes the start date or duration.
// When editing, the stored end date is kept until one of them changes.
watch([() => form.start_date, duration], ([newStart, newDuration]) => {
    if (newStart && Number(newDuration) > 0) {
        form.end_date = subscriptionEndDate(newStart, Number(newDuration));
    }
});

// With a package selected the amount is calculated from its price; without
// one, the amount is typed in by hand.
const calculateAmount = () => {
    const pkg = selectedPackage.value;

    if (!pkg || pkg.price === undefined || pkg.price === null) {
        return;
    }

    if (isPerStudent.value) {
        const count = parseInt(form.student_count as any) || 0;
        form.amount = (parseFloat(pkg.price) * count).toString();
    } else {
        form.amount = (
            parseFloat(pkg.price) * Number(duration.value)
        ).toString();
    }
};

watch([selectedPackage, duration, () => form.student_count], calculateAmount, {
    immediate: !isEditing,
});

// Clear the package when the service changes, since packages belong to a service.
watch(
    () => form.service_id,
    (newServiceId, oldServiceId) => {
        if (oldServiceId && newServiceId !== oldServiceId) {
            form.package_id = '';
            form.modules = [];
        }
    },
);

// Auto-select the package's modules and reset an unsupported mode.
watch(
    () => form.package_id,
    (newPackageId) => {
        if (!newPackageId) {
            return;
        }

        const pkg = availablePackages.value.find(
            (p: any) => p.id === newPackageId,
        );

        if (pkg?.modules) {
            const moduleIds = pkg.modules.map((m: any) => m.id);
            form.modules = [...new Set([...form.modules, ...moduleIds])];
        }

        if (!availableModes.value.includes(form.mode) && !isRenewing) {
            form.mode = '';
        }
    },
);

const submit = () => {
    if (isEditing) {
        form.put(subscriptionsRoutes.update.url(props.subscription.id));
    } else if (isRenewing) {
        form.post(subscriptionsRoutes.renew.url(props.renewing.id));
    } else {
        form.post(subscriptionsRoutes.store.url());
    }
};

const title = computed(() => {
    if (isEditing) return 'Edit Subscription';
    if (isRenewing)
        return `Renew Subscription: ${props.renewing.business?.name}`;
    return 'New Subscription';
});
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1
                    class="text-foreground text-2xl font-semibold tracking-tight md:text-3xl"
                >
                    {{ title }}
                </h1>
            </div>
            <Link
                :href="subscriptionsRoutes.index.url()"
                class="text-muted-foreground hover:text-foreground text-sm font-medium"
            >
                &larr; Back to Subscriptions
            </Link>
        </div>

        <div
            class="border-border bg-card rounded-xl border p-6 shadow-sm md:p-8"
        >
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Select Business & Service -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Business
                            <span class="text-destructive">*</span></label
                        >
                        <select
                            v-model="form.business_id"
                            required
                            :disabled="isRenewing"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 disabled:bg-muted w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] disabled:opacity-50 sm:text-sm"
                        >
                            <option value="">Select Business</option>
                            <option
                                v-for="b in props.businesses"
                                :key="b.id"
                                :value="b.id"
                            >
                                {{ b.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.business_id"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.business_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Service
                            <span class="text-destructive">*</span></label
                        >
                        <select
                            v-model="form.service_id"
                            required
                            :disabled="isRenewing"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 disabled:bg-muted w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] disabled:opacity-50 sm:text-sm"
                        >
                            <option value="">Select Service</option>
                            <option
                                v-for="s in props.services"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.service_id"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.service_id }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Mode <span class="text-destructive">*</span></label
                        >
                        <select
                            v-model="form.mode"
                            required
                            :disabled="isRenewing"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 disabled:bg-muted w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] disabled:opacity-50 sm:text-sm"
                        >
                            <option value="">Select Mode</option>
                            <option
                                v-for="m in availableModes"
                                :key="m"
                                :value="m"
                                class="capitalize"
                            >
                                {{ m }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.mode"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.mode }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="
                        form.service_id &&
                        (availablePackages.length > 0 ||
                            availableModules.length > 0)
                    "
                    class="border-border bg-muted/30 space-y-4 rounded-xl border p-4"
                >
                    <h3 class="text-foreground text-sm font-semibold">
                        Service Configuration
                    </h3>

                    <div v-if="availablePackages.length > 0">
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Select Package (Optional)</label
                        >
                        <select
                            v-model="form.package_id"
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        >
                            <option value="">No package (custom amount)</option>
                            <option
                                v-for="pkg in availablePackages"
                                :key="pkg.id"
                                :value="pkg.id"
                            >
                                {{ pkg.name }} ({{
                                    pkg.price
                                        ? Number(pkg.price).toLocaleString(
                                              'en-US',
                                          ) +
                                          (pkg.pricing_model === 'per_student'
                                              ? ' FCFA/student'
                                              : ' FCFA/mo')
                                        : 'Free'
                                }})
                            </option>
                        </select>
                        <p
                            v-if="form.errors.package_id"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.package_id }}
                        </p>

                        <!-- Student Population Input for Per-Student Packages -->
                        <div v-if="isPerStudent" class="mt-3">
                            <label
                                class="text-foreground mb-1 block text-sm font-medium"
                            >
                                Enrolled Student Population
                                <span class="text-destructive">*</span>
                            </label>
                            <input
                                v-model.number="form.student_count"
                                type="number"
                                min="1"
                                required
                                placeholder="e.g. 850"
                                class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                            />
                            <p class="text-muted-foreground mt-1 text-xs">
                                Used to compute total contract value ({{
                                    selectedPackage?.price
                                        ? Number(
                                              selectedPackage.price,
                                          ).toLocaleString('en-US')
                                        : 0
                                }}
                                FCFA per student).
                            </p>
                            <p
                                v-if="form.errors.student_count"
                                class="text-destructive mt-1 text-sm"
                            >
                                {{ form.errors.student_count }}
                            </p>
                        </div>

                        <!-- Notice for Student-Funded MoMo Packages -->
                        <div
                            v-if="isStudentMoMo"
                            class="border-info/30 bg-info/10 text-info mt-3 rounded-lg border p-3 text-xs"
                        >
                            <strong>📱 Student Mobile Money Tier:</strong> This
                            subscription amount serves as the annual target.
                            Collections from students will be recorded on the
                            subscription details page as Mobile Money receipts
                            arrive.
                        </div>
                    </div>

                    <div v-if="availableModules.length > 0">
                        <label
                            class="text-foreground mb-2 block text-sm font-medium"
                            >Included Modules</label
                        >
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label
                                v-for="mod in availableModules"
                                :key="mod.id"
                                class="border-border bg-background hover:bg-muted flex cursor-pointer items-start space-x-3 rounded-lg border p-3 transition-colors"
                            >
                                <div class="flex h-5 items-center">
                                    <input
                                        type="checkbox"
                                        :value="mod.id"
                                        v-model="form.modules"
                                        class="border-input bg-background text-primary focus:ring-primary h-4 w-4 rounded"
                                    />
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-foreground text-sm font-medium"
                                        >{{ mod.name }}</span
                                    >
                                    <span
                                        v-if="mod.description"
                                        class="text-muted-foreground text-xs"
                                        >{{ mod.description }}</span
                                    >
                                </div>
                            </label>
                        </div>
                        <p
                            v-if="form.errors.modules"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.modules }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Start Date
                            <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="form.start_date"
                            type="date"
                            required
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        />
                        <p
                            v-if="form.errors.start_date"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.start_date }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Duration (Months)
                            <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="duration"
                            type="number"
                            min="1"
                            step="1"
                            required
                            class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >End Date (last day)
                            <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="form.end_date"
                            type="date"
                            required
                            readonly
                            class="border-input bg-muted text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full cursor-not-allowed rounded-md border px-3 py-2 opacity-80 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        />
                        <p
                            v-if="form.errors.end_date"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.end_date }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="text-foreground mb-1 block text-sm font-medium"
                            >Amount (FCFA)
                            <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="form.amount"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            :readonly="!!selectedPackage"
                            :class="
                                selectedPackage
                                    ? 'bg-muted cursor-not-allowed opacity-80'
                                    : 'bg-background'
                            "
                            class="border-input text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                        />
                        <p class="text-muted-foreground mt-1 text-xs">
                            <template v-if="!selectedPackage"
                                >Enter the agreed contract amount.</template
                            >
                            <template v-else-if="isPerStudent"
                                >Amount is auto-calculated: Enrolled Students ×
                                Rate per student.</template
                            >
                            <template v-else
                                >Amount is auto-calculated based on package
                                monthly price × duration.</template
                            >
                        </p>
                        <p
                            v-if="form.errors.amount"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.amount }}
                        </p>
                    </div>
                </div>

                <div>
                    <label
                        class="text-foreground mb-1 block text-sm font-medium"
                        >Notes</label
                    >
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 shadow-xs outline-none focus-visible:ring-[3px] sm:text-sm"
                    ></textarea>
                    <p
                        v-if="form.errors.notes"
                        class="text-destructive mt-1 text-sm"
                    >
                        {{ form.errors.notes }}
                    </p>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="buttonVariants({ variant: 'default' })"
                        class="disabled:opacity-50"
                    >
                        {{
                            isEditing
                                ? 'Update Subscription'
                                : isRenewing
                                  ? 'Confirm Renewal'
                                  : 'Save Subscription'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
