<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref } from 'vue';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps<{
    report: {
        fiscal_year: number;
        budget: any;
        rows: any[];
        totals: {
            expected_amount: number;
            actual_amount: number;
            expected_licenses: number;
            current_licenses: number;
            percent_to_goal: number;
            variance: number;
        };
    };
    availableYears: number[];
    selectedYear: number;
}>();

const formatMoney = (val: number) =>
    val ? Number(val).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' FCFA' : '0 FCFA';

const changeYear = (year: number) => {
    router.get(route('budgets.index'), { year }, { preserveState: false });
};
</script>

<template>
    <Head title="Budget Report" />

    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-8">
        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Budget Report</h1>
                <p class="mt-1 text-sm text-muted-foreground">Accrual-basis revenue budget by fiscal year</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Year Selector -->
                <div class="flex rounded-lg border border-border overflow-hidden">
                    <button
                        v-for="year in availableYears"
                        :key="year"
                        @click="changeYear(year)"
                        :class="[
                            'px-3 py-1.5 text-sm font-medium transition-colors',
                            year === selectedYear
                                ? 'bg-orange-500 text-white'
                                : 'bg-background text-muted-foreground hover:bg-muted'
                        ]"
                    >
                        {{ year }}
                    </button>
                </div>
                <Link
                    :href="route('budgets.create', { year: selectedYear })"
                    :class="buttonVariants({ variant: 'default' })"
                >
                    {{ report.budget ? 'Edit Budget' : 'Setup Budget' }}
                </Link>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Annual Goal -->
            <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Annual Goal</p>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ formatMoney(report.totals.expected_amount) }}</p>
                <p class="mt-1 text-xs text-muted-foreground">Fiscal Year {{ selectedYear }}</p>
            </div>
            <!-- Actual Accrued -->
            <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Actual Revenue (Accrued)</p>
                <p class="mt-2 text-2xl font-bold text-orange-500">{{ formatMoney(report.totals.actual_amount) }}</p>
                <p class="mt-1 text-xs text-muted-foreground">Pro-rata across active subscriptions</p>
            </div>
            <!-- % to Goal -->
            <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">% to Goal</p>
                <p
                    class="mt-2 text-2xl font-bold"
                    :class="report.totals.percent_to_goal >= 100 ? 'text-green-500' : 'text-foreground'"
                >
                    {{ report.totals.percent_to_goal }}%
                </p>
                <div class="mt-2 w-full rounded-full bg-muted h-1.5">
                    <div
                        class="h-1.5 rounded-full transition-all"
                        :class="report.totals.percent_to_goal >= 100 ? 'bg-green-500' : 'bg-orange-500'"
                        :style="{ width: Math.min(report.totals.percent_to_goal, 100) + '%' }"
                    ></div>
                </div>
            </div>
            <!-- Variance -->
            <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Variance</p>
                <p
                    class="mt-2 text-2xl font-bold"
                    :class="report.totals.variance <= 0 ? 'text-green-500' : 'text-destructive'"
                >
                    {{ report.totals.variance <= 0 ? '+' : '-' }}{{ formatMoney(Math.abs(report.totals.variance)) }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ report.totals.variance <= 0 ? 'Above target' : 'Below target' }}
                </p>
            </div>
        </div>

        <!-- Budget Table -->
        <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <h2 class="text-base font-semibold text-foreground">Service Breakdown — {{ selectedYear }}</h2>
            </div>

            <div v-if="report.rows.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-orange-500 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Fiscal Year</th>
                            <th class="px-4 py-3 text-left font-medium">Service</th>
                            <th class="px-4 py-3 text-right font-medium">Current Licenses</th>
                            <th class="px-4 py-3 text-right font-medium">Expected Licenses</th>
                            <th class="px-4 py-3 text-right font-medium">Actual Amount</th>
                            <th class="px-4 py-3 text-right font-medium">Expected Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="row in report.rows" :key="row.service_id" class="hover:bg-muted/40 transition-colors">
                            <td class="px-4 py-3 text-foreground font-medium">{{ row.fiscal_year }}</td>
                            <td class="px-4 py-3 text-foreground font-medium">{{ row.service_name }}</td>
                            <td class="px-4 py-3 text-right text-foreground">{{ row.current_licenses }}</td>
                            <td class="px-4 py-3 text-right text-muted-foreground">{{ row.expected_licenses }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-orange-600 dark:text-orange-400">
                                {{ formatMoney(row.actual_amount) }}
                            </td>
                            <td class="px-4 py-3 text-right text-muted-foreground">
                                {{ formatMoney(row.expected_amount) }}
                            </td>
                        </tr>
                    </tbody>
                    <!-- Totals Footer -->
                    <tfoot class="border-t-2 border-border bg-muted/40">
                        <tr>
                            <td colspan="2" class="px-4 py-3 font-bold text-foreground">TOTAL</td>
                            <td class="px-4 py-3 text-right font-bold text-foreground">{{ report.totals.current_licenses }}</td>
                            <td class="px-4 py-3 text-right font-bold text-muted-foreground">{{ report.totals.expected_licenses }}</td>
                            <td class="px-4 py-3 text-right font-bold text-orange-600 dark:text-orange-400">{{ formatMoney(report.totals.actual_amount) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-muted-foreground">{{ formatMoney(report.totals.expected_amount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                <p class="text-muted-foreground text-sm">No budget data for {{ selectedYear }}.</p>
                <Link
                    :href="route('budgets.create', { year: selectedYear })"
                    :class="[buttonVariants({ variant: 'default' }), 'mt-4']"
                >
                    Setup Budget for {{ selectedYear }}
                </Link>
            </div>
        </div>
    </div>
</template>
