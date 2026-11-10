<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import budgetsRoutes from '@/routes/budgets';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button, buttonVariants } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import { card } from '@/lib/ui';

const props = defineProps<{
    fiscalYear: number;
    services: any[];
    existingTargets: Record<
        number,
        {
            expected_licenses: number;
            avg_student_count?: number;
            expected_months?: number;
            expected_amount: number;
        }
    >;
    existingBudget: any;
}>();

// Build initial package targets from services
const buildInitialTargets = () => {
    const targets: Record<
        number,
        {
            expected_licenses: number;
            avg_student_count: number;
            expected_months: number;
        }
    > = {};
    props.services.forEach((service) => {
        service.packages?.forEach((pkg: any) => {
            const existing = props.existingTargets[pkg.id];
            targets[pkg.id] = {
                expected_licenses: existing ? existing.expected_licenses : 0,
                avg_student_count: existing
                    ? existing.avg_student_count || 0
                    : pkg.pricing_model === 'per_student'
                      ? 500
                      : 0,
                expected_months: existing?.expected_months || 12,
            };
        });
    });
    return targets;
};

const targets = ref(buildInitialTargets());

// Expected revenue for one package line. Must match
// BudgetPackage::calculateExpectedAmount() on the server.
//  - per-student: schools × average students × price per student
//  - flat:        licences × monthly price × months
const getExpectedAmount = (pkg: any): number => {
    const target = targets.value[pkg.id];
    const licenses = target?.expected_licenses || 0;
    const price = parseFloat(pkg.price || 0);

    if (pkg.pricing_model === 'per_student') {
        return licenses * (target?.avg_student_count || 0) * price;
    }

    return licenses * price * (target?.expected_months || 0);
};

// Compute grand total
const grandTotal = computed(() => {
    let total = 0;
    props.services.forEach((service) => {
        service.packages?.forEach((pkg: any) => {
            total += getExpectedAmount(pkg);
        });
    });
    return total;
});

const formatMoney = (val: number) =>
    val
        ? Number(val).toLocaleString('en-US', {
              minimumFractionDigits: 0,
              maximumFractionDigits: 0,
          }) + ' FCFA'
        : '0 FCFA';

const isSubmitting = ref(false);

const submit = () => {
    isSubmitting.value = true;

    const packages: any[] = [];
    props.services.forEach((service) => {
        service.packages?.forEach((pkg: any) => {
            const licenses = targets.value[pkg.id]?.expected_licenses || 0;
            if (licenses > 0) {
                packages.push({
                    package_id: pkg.id,
                    expected_licenses: licenses,
                    avg_student_count:
                        pkg.pricing_model === 'per_student'
                            ? targets.value[pkg.id]?.avg_student_count || 0
                            : null,
                    expected_months:
                        pkg.pricing_model === 'per_student'
                            ? null
                            : targets.value[pkg.id]?.expected_months || 12,
                    expected_amount: getExpectedAmount(pkg),
                });
            }
        });
    });

    const form = useForm({
        fiscal_year: props.fiscalYear,
        packages,
    });

    form.post(budgetsRoutes.store.url(), {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`${fiscalYear} budget`" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="`${existingBudget ? 'Edit' : 'Set up'} ${fiscalYear} budget`"
            description="Enter how many licences you expect to sell per package. For monthly packages, also say how many months they'll run this year."
            :back-href="
                budgetsRoutes.index.url({ query: { year: fiscalYear } })
            "
            back-label="Back to budget"
        />

        <!-- Running total -->
        <div
            class="bg-primary text-primary-foreground flex flex-col gap-3 rounded-xl p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p class="text-sm font-medium opacity-80">
                    Annual goal for {{ fiscalYear }}
                </p>
                <p
                    class="mt-1 text-3xl font-semibold tracking-tight tabular-nums"
                >
                    {{ formatMoney(grandTotal) }}
                </p>
            </div>
            <p class="max-w-xs text-xs opacity-80 sm:text-right">
                Monthly packages: licences × monthly price × months. Per-student
                packages: schools × students × price.
            </p>
        </div>

        <!-- Services & Packages -->
        <form @submit.prevent="submit" class="space-y-4">
            <div
                v-for="service in services"
                :key="service.id"
                :class="card.base"
            >
                <div class="border-border border-b px-5 py-4">
                    <h2 :class="card.title">
                        {{ service.name }}
                    </h2>
                    <p
                        v-if="service.description"
                        class="text-muted-foreground mt-0.5 text-xs"
                    >
                        {{ service.description }}
                    </p>
                </div>

                <div
                    v-if="service.packages && service.packages.length > 0"
                    class="divide-border divide-y"
                >
                    <div
                        v-for="pkg in service.packages"
                        :key="pkg.id"
                        class="flex flex-col gap-4 px-5 py-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="text-foreground text-sm font-medium">
                                {{ pkg.name }}
                            </p>
                            <p class="text-muted-foreground mt-0.5 text-xs">
                                {{
                                    pkg.price
                                        ? Number(pkg.price).toLocaleString(
                                              'en-US',
                                          ) +
                                          (pkg.pricing_model === 'per_student'
                                              ? ' FCFA / student'
                                              : ' FCFA / month')
                                        : 'Free'
                                }}
                                <Badge
                                    v-if="pkg.pricing_model === 'per_student'"
                                    :variant="
                                        pkg.payer_type === 'student_momo'
                                            ? 'warning'
                                            : 'info'
                                    "
                                    class="ml-2"
                                >
                                    {{
                                        pkg.payer_type === 'student_momo'
                                            ? 'Students pay (MoMo)'
                                            : 'School pays'
                                    }}
                                </Badge>
                                <span
                                    v-if="
                                        pkg.supported_modes &&
                                        pkg.supported_modes.length
                                    "
                                    class="ml-2"
                                >
                                    · {{ pkg.supported_modes.join(', ') }}
                                </span>
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-end gap-3">
                            <div>
                                <label
                                    class="text-muted-foreground mb-1 block text-xs md:text-right"
                                >
                                    {{
                                        pkg.pricing_model === 'per_student'
                                            ? 'Schools'
                                            : 'Licences'
                                    }}
                                </label>
                                <input
                                    v-model.number="
                                        targets[pkg.id].expected_licenses
                                    "
                                    type="number"
                                    min="0"
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-24 rounded-md border px-3 py-1.5 text-center text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>

                            <!-- Avg Students input for per-student packages -->
                            <div v-if="pkg.pricing_model === 'per_student'">
                                <label
                                    class="text-muted-foreground mb-1 block text-xs md:text-right"
                                    >Students per school</label
                                >
                                <input
                                    v-model.number="
                                        targets[pkg.id].avg_student_count
                                    "
                                    type="number"
                                    min="0"
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-24 rounded-md border px-3 py-1.5 text-center text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>

                            <!-- Months input for monthly (flat) packages -->
                            <div v-else>
                                <label
                                    class="text-muted-foreground mb-1 block text-xs md:text-right"
                                    >Months this year</label
                                >
                                <input
                                    v-model.number="
                                        targets[pkg.id].expected_months
                                    "
                                    type="number"
                                    min="1"
                                    max="12"
                                    class="border-input bg-background text-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-24 rounded-md border px-3 py-1.5 text-center text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>

                            <div class="min-w-35 text-right">
                                <p class="text-muted-foreground mb-1 text-xs">
                                    Expected
                                </p>
                                <p
                                    class="text-foreground text-sm font-semibold tabular-nums"
                                >
                                    {{ formatMoney(getExpectedAmount(pkg)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="text-muted-foreground px-5 py-4 text-xs italic"
                >
                    No packages for this service yet.
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-2">
                <Link
                    :href="
                        budgetsRoutes.index.url({ query: { year: fiscalYear } })
                    "
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    Cancel
                </Link>
                <Button
                    type="submit"
                    :disabled="isSubmitting || grandTotal === 0"
                >
                    {{ isSubmitting ? 'Saving…' : `Save ${fiscalYear} budget` }}
                </Button>
            </div>
        </form>
    </div>
</template>
