<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    fiscalYear: number;
    services: any[];
    existingTargets: Record<number, { expected_licenses: number; avg_student_count?: number; expected_amount: number }>;
    existingBudget: any;
}>();

// Build initial package targets from services
const buildInitialTargets = () => {
    const targets: Record<number, { expected_licenses: number; avg_student_count: number }> = {};
    props.services.forEach(service => {
        service.packages?.forEach((pkg: any) => {
            const existing = props.existingTargets[pkg.id];
            targets[pkg.id] = {
                expected_licenses: existing ? existing.expected_licenses : 0,
                avg_student_count: existing ? (existing.avg_student_count || 0) : (pkg.pricing_model === 'per_student' ? 500 : 0),
            };
        });
    });
    return targets;
};

const targets = ref(buildInitialTargets());

// Dynamically compute expected_amount for each package
const getExpectedAmount = (pkg: any): number => {
    const licenses = targets.value[pkg.id]?.expected_licenses || 0;
    if (pkg.pricing_model === 'per_student') {
        const avgStudents = targets.value[pkg.id]?.avg_student_count || 0;
        return licenses * avgStudents * parseFloat(pkg.price || 0);
    }
    return licenses * parseFloat(pkg.price || 0);
};

// Compute grand total
const grandTotal = computed(() => {
    let total = 0;
    props.services.forEach(service => {
        service.packages?.forEach((pkg: any) => {
            total += getExpectedAmount(pkg);
        });
    });
    return total;
});

const formatMoney = (val: number) =>
    val ? Number(val).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' FCFA' : '0 FCFA';

const isSubmitting = ref(false);

const submit = () => {
    isSubmitting.value = true;

    const packages: any[] = [];
    props.services.forEach(service => {
        service.packages?.forEach((pkg: any) => {
            const licenses = targets.value[pkg.id]?.expected_licenses || 0;
            if (licenses > 0) {
                packages.push({
                    package_id: pkg.id,
                    expected_licenses: licenses,
                    avg_student_count: pkg.pricing_model === 'per_student' ? (targets.value[pkg.id]?.avg_student_count || 0) : null,
                    expected_amount: getExpectedAmount(pkg),
                });
            }
        });
    });

    const form = useForm({
        fiscal_year: props.fiscalYear,
        packages,
    });

    form.post(route('budgets.store'), {
        onFinish: () => { isSubmitting.value = false; }
    });
};
</script>

<template>
    <Head :title="`Setup Budget ${fiscalYear}`" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    {{ existingBudget ? 'Edit' : 'Setup' }} Budget — {{ fiscalYear }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Set the expected number of licenses per package. The system will calculate the expected revenue automatically.
                </p>
            </div>
            <Link :href="route('budgets.index', { year: fiscalYear })" :class="buttonVariants({ variant: 'outline' })">
                Cancel
            </Link>
        </div>

        <!-- Grand Total Banner -->
        <div class="rounded-2xl border border-orange-200 bg-orange-50 dark:bg-orange-950/20 dark:border-orange-900 p-5 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-orange-700 dark:text-orange-400">Total Expected Revenue for {{ fiscalYear }}</p>
                <p class="mt-1 text-3xl font-bold text-orange-600 dark:text-orange-300">{{ formatMoney(grandTotal) }}</p>
            </div>
            <p class="text-xs text-orange-500 dark:text-orange-400 max-w-xs text-right">
                Automatically calculated from expected licenses × monthly package prices.
            </p>
        </div>

        <!-- Services & Packages -->
        <form @submit.prevent="submit" class="space-y-4">
            <div v-for="service in services" :key="service.id" class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-border bg-muted/30">
                    <h2 class="text-sm font-semibold text-foreground">{{ service.name }}</h2>
                    <p v-if="service.description" class="mt-0.5 text-xs text-muted-foreground">{{ service.description }}</p>
                </div>

                <div v-if="service.packages && service.packages.length > 0" class="divide-y divide-border">
                    <div
                        v-for="pkg in service.packages"
                        :key="pkg.id"
                        class="flex items-center justify-between gap-4 px-5 py-4"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-foreground">{{ pkg.name }}</p>
                            <p class="text-xs text-muted-foreground mt-0.5">
                                {{ pkg.price ? Number(pkg.price).toLocaleString('en-US') + (pkg.pricing_model === 'per_student' ? ' FCFA / student' : ' FCFA / month') : 'Free' }}
                                <span v-if="pkg.pricing_model === 'per_student'" class="ml-2 font-medium text-orange-600 dark:text-orange-400">
                                    · {{ pkg.payer_type === 'student_momo' ? '📱 Student MoMo' : '🏫 School Pays' }}
                                </span>
                                <span v-if="pkg.supported_modes && pkg.supported_modes.length" class="ml-2">
                                    · {{ pkg.supported_modes.join(', ') }}
                                </span>
                            </p>
                        </div>

                        <div class="flex items-center gap-3 flex-shrink-0">
                            <div>
                                <label class="block text-xs text-muted-foreground mb-1 text-right">
                                    {{ pkg.pricing_model === 'per_student' ? 'Target Schools' : 'Expected Licenses' }}
                                </label>
                                <input
                                    v-model.number="targets[pkg.id].expected_licenses"
                                    type="number"
                                    min="0"
                                    class="w-24 text-center rounded-md border border-input bg-background text-foreground px-3 py-1.5 text-sm focus:border-primary focus:ring-primary"
                                />
                            </div>

                            <!-- Avg Students input for per-student packages -->
                            <div v-if="pkg.pricing_model === 'per_student'">
                                <label class="block text-xs text-muted-foreground mb-1 text-right">Avg. Students / School</label>
                                <input
                                    v-model.number="targets[pkg.id].avg_student_count"
                                    type="number"
                                    min="0"
                                    class="w-24 text-center rounded-md border border-input bg-background text-foreground px-3 py-1.5 text-sm focus:border-primary focus:ring-primary"
                                />
                            </div>

                            <div class="text-right min-w-[140px]">
                                <p class="text-xs text-muted-foreground mb-1">Expected Amount</p>
                                <p class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                                    {{ formatMoney(getExpectedAmount(pkg)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="px-5 py-4 text-xs text-muted-foreground italic">
                    No packages configured for this service.
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-2">
                <Link :href="route('budgets.index', { year: fiscalYear })" :class="buttonVariants({ variant: 'outline' })">
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="isSubmitting || grandTotal === 0"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    {{ isSubmitting ? 'Saving...' : `Save ${fiscalYear} Budget` }}
                </button>
            </div>
        </form>
    </div>
</template>
