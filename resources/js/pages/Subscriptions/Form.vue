<script setup lang="ts">
import { route } from 'ziggy-js';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, watch, ref, onMounted } from 'vue';
import { buttonVariants } from '@/components/ui/button';

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

// Determine initial start date. If renewing, use the old end date.
const initialStartDate = isRenewing 
    ? sourceData.end_date 
    : (sourceData.start_date || new Date().toISOString().split('T')[0]);

const form = useForm({
    business_id: sourceData.business_id || props.defaultBusinessId || '',
    service_id: sourceData.service_id || '',
    package_id: sourceData.package_id || '',
    modules: sourceData.modules?.map((m: any) => m.id) || [],
    mode: sourceData.mode || '',
    amount: sourceData.amount || '',
    student_count: sourceData.student_count || '',
    start_date: initialStartDate,
    end_date: sourceData.end_date || '',
    notes: sourceData.notes || '',
});

// A local ref for duration (in months)
const duration = ref(1);

// Calculate initial duration if editing
if (isEditing && form.start_date && form.end_date) {
    const start = new Date(form.start_date);
    const end = new Date(form.end_date);
    let months = (end.getFullYear() - start.getFullYear()) * 12;
    months -= start.getMonth();
    months += end.getMonth();
    duration.value = months > 0 ? months : 1;
}

const selectedService = computed(() => {
    return props.services.find((s) => s.id === form.service_id);
});

const availablePackages = computed(() => {
    return selectedService.value?.packages || [];
});

const availableModules = computed(() => {
    return selectedService.value?.modules || [];
});

// Filter supported modes based on the selected package
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
    if (selectedPackage.value && selectedPackage.value.supported_modes) {
        return selectedPackage.value.supported_modes;
    }
    // If no package selected, allow all by default
    return ['online', 'offline', 'hybrid'];
});

// Calculate end_date when start_date or duration changes
watch([() => form.start_date, duration], ([newStart, newDuration]) => {
    if (newStart && newDuration > 0) {
        const date = new Date(newStart);
        date.setMonth(date.getMonth() + Number(newDuration));
        form.end_date = date.toISOString().split('T')[0];
    }
}, { immediate: true });

// Auto-calculate amount when package, duration, or student_count changes
watch([selectedPackage, duration, () => form.student_count], ([pkg, dur, students]) => {
    if (pkg && pkg.price !== undefined) {
        if (isPerStudent.value) {
            const count = parseInt(students as any) || 0;
            form.amount = (parseFloat(pkg.price) * count).toString();
        } else {
            form.amount = (parseFloat(pkg.price) * dur).toString();
        }
    }
}, { immediate: !isEditing });

// Auto-select modules when package changes
watch(() => form.package_id, (newPackageId) => {
    if (newPackageId) {
        const pkg = availablePackages.value.find((p: any) => p.id === newPackageId);
        if (pkg && pkg.modules) {
            const moduleIds = pkg.modules.map((m: any) => m.id);
            form.modules = [...new Set([...form.modules, ...moduleIds])];
        }
        
        // Reset mode if the current one isn't supported
        if (pkg && pkg.supported_modes && !pkg.supported_modes.includes(form.mode)) {
            form.mode = '';
        }
    }
});

const submit = () => {
    if (isEditing) {
        form.put(route('subscriptions.update', props.subscription.id));
    } else if (isRenewing) {
        form.post(route('subscriptions.renew', props.renewing.id));
    } else {
        form.post(route('subscriptions.store'));
    }
};

const title = computed(() => {
    if (isEditing) return 'Edit Subscription';
    if (isRenewing) return `Renew Subscription: ${props.renewing.business?.name}`;
    return 'New Subscription';
});
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ title }}</h1>
            </div>
            <Link
                :href="'/dashboard'"
                class="text-sm font-medium text-muted-foreground hover:text-foreground"
            >
                &larr; Back to Dashboard
            </Link>
        </div>

        <div class="rounded-2xl border border-border bg-card p-6 md:p-8 shadow-sm">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Select Business & Service -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Business <span class="text-destructive">*</span></label
                        >
                        <select
                            v-model="form.business_id"
                            required
                            :disabled="isRenewing"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary disabled:bg-muted disabled:opacity-50 sm:text-sm"
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
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.business_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Service <span class="text-destructive">*</span></label
                        >
                        <select
                            v-model="form.service_id"
                            required
                            :disabled="isRenewing"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary disabled:bg-muted disabled:opacity-50 sm:text-sm"
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
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.service_id }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Mode <span class="text-destructive">*</span></label
                        >
                        <select
                            v-model="form.mode"
                            required
                            :disabled="isRenewing"
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary disabled:bg-muted disabled:opacity-50 sm:text-sm"
                        >
                            <option value="">Select Mode</option>
                            <option v-for="m in availableModes" :key="m" :value="m" class="capitalize">{{ m }}</option>
                        </select>
                        <p
                            v-if="form.errors.mode"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.mode }}
                        </p>
                    </div>
                </div>

                <div v-if="form.service_id && (availablePackages.length > 0 || availableModules.length > 0)" class="rounded-xl border border-border bg-muted/30 p-4 space-y-4">
                    <h3 class="text-sm font-semibold text-foreground">Service Configuration</h3>
                    
                    <div v-if="availablePackages.length > 0">
                        <label class="mb-1 block text-sm font-medium text-foreground">Select Package (Optional)</label>
                        <select v-model="form.package_id" class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="" disabled>Select a package</option>
                            <option v-for="pkg in availablePackages" :key="pkg.id" :value="pkg.id">
                                {{ pkg.name }} ({{ pkg.price ? Number(pkg.price).toLocaleString('en-US') + (pkg.pricing_model === 'per_student' ? ' FCFA/student' : ' FCFA/mo') : 'Free' }})
                            </option>
                        </select>
                        <p v-if="form.errors.package_id" class="mt-1 text-sm text-destructive">{{ form.errors.package_id }}</p>

                        <!-- Student Population Input for Per-Student Packages -->
                        <div v-if="isPerStudent" class="mt-3">
                            <label class="mb-1 block text-sm font-medium text-foreground">
                                Enrolled Student Population <span class="text-destructive">*</span>
                            </label>
                            <input
                                v-model.number="form.student_count"
                                type="number"
                                min="1"
                                required
                                placeholder="e.g. 850"
                                class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">
                                Used to compute total contract value ({{ selectedPackage?.price ? Number(selectedPackage.price).toLocaleString('en-US') : 0 }} FCFA per student).
                            </p>
                            <p v-if="form.errors.student_count" class="mt-1 text-sm text-destructive">
                                {{ form.errors.student_count }}
                            </p>
                        </div>

                        <!-- Notice for Student-Funded MoMo Packages -->
                        <div v-if="isStudentMoMo" class="mt-3 rounded-lg border border-blue-200 bg-blue-50 dark:bg-blue-950/20 dark:border-blue-900 p-3 text-xs text-blue-700 dark:text-blue-300">
                            <strong>📱 Student Mobile Money Tier:</strong> This subscription amount serves as the annual target. Collections from students will be recorded on the subscription details page as Mobile Money receipts arrive.
                        </div>
                    </div>

                    <div v-if="availableModules.length > 0">
                        <label class="mb-2 block text-sm font-medium text-foreground">Included Modules</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label v-for="mod in availableModules" :key="mod.id" class="flex items-start space-x-3 rounded-lg border border-border bg-background p-3 hover:bg-muted cursor-pointer transition-colors">
                                <div class="flex h-5 items-center">
                                    <input 
                                        type="checkbox" 
                                        :value="mod.id" 
                                        v-model="form.modules"
                                        class="h-4 w-4 rounded border-input bg-background text-primary focus:ring-primary" 
                                    />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">{{ mod.name }}</span>
                                    <span v-if="mod.description" class="text-xs text-muted-foreground">{{ mod.description }}</span>
                                </div>
                            </label>
                        </div>
                        <p v-if="form.errors.modules" class="mt-1 text-sm text-destructive">{{ form.errors.modules }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Start Date
                            <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="form.start_date"
                            type="date"
                            required
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                        />
                        <p
                            v-if="form.errors.start_date"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.start_date }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Duration (Months) <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="duration"
                            type="number"
                            min="1"
                            step="1"
                            required
                            class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                        />
                    </div>
                    
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >End Date <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="form.end_date"
                            type="date"
                            required
                            readonly
                            class="w-full rounded-md border border-input bg-muted text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm opacity-80 cursor-not-allowed"
                        />
                        <p
                            v-if="form.errors.end_date"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.end_date }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-foreground"
                            >Amount (FCFA)
                            <span class="text-destructive">*</span></label
                        >
                        <input
                            v-model="form.amount"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            readonly
                            class="w-full rounded-md border border-input bg-muted text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm opacity-80 cursor-not-allowed"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ isPerStudent ? 'Amount is auto-calculated: Enrolled Students × Rate per student.' : 'Amount is auto-calculated based on package monthly price × duration.' }}
                        </p>
                        <p
                            v-if="form.errors.amount"
                            class="mt-1 text-sm text-destructive"
                        >
                            {{ form.errors.amount }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-foreground"
                        >Notes</label
                    >
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background text-foreground px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm"
                    ></textarea>
                    <p
                        v-if="form.errors.notes"
                        class="mt-1 text-sm text-destructive"
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
