<script setup lang="ts">
import { table } from '@/lib/ui';
import { Head, Link, router } from '@inertiajs/vue3';
import budgetsRoutes from '@/routes/budgets';
import { ref } from 'vue';
import { buttonVariants } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import { Banknote, Goal, Scale, TrendingUp } from '@lucide/vue';

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

const number = (val: number) =>
    Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
const formatMoney = (val: number) => number(val) + ' FCFA';

const changeYear = (year: number) => {
    router.get(budgetsRoutes.index.url(), { year }, { preserveState: false });
};
</script>

<template>
    <Head title="Budget Report" />

    <div class="flex flex-col gap-6 p-4 md:p-8">
        <PageHeader title="Budget Report" description="Revenue targets against what each fiscal year has actually earned.">
            <template #actions>
                <div class="inline-flex rounded-md border border-border bg-card p-0.5 shadow-xs" role="group" aria-label="Fiscal year">
                    <button
                        v-for="year in availableYears"
                        :key="year"
                        type="button"
                        @click="changeYear(year)"
                        :aria-pressed="year === selectedYear"
                        :class="[
                            'rounded px-3 py-1 text-sm font-medium tabular-nums transition-colors',
                            year === selectedYear
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                        ]"
                    >
                        {{ year }}
                    </button>
                </div>
                <Link :href="budgetsRoutes.create.url({ query: { year: selectedYear } })" :class="buttonVariants()">
                    {{ report.budget ? 'Edit budget' : 'Set up budget' }}
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Annual goal" :value="number(report.totals.expected_amount)" unit="FCFA" :hint="'Target for fiscal year ' + selectedYear" tone="primary">
                <template #icon><Goal /></template>
            </StatCard>

            <StatCard label="Earned so far" :value="number(report.totals.actual_amount)" unit="FCFA" tone="accent">
                <template #icon><Banknote /></template>
                Only the part of each subscription that falls inside {{ selectedYear }}
            </StatCard>

            <StatCard
                label="Progress to goal"
                :value="report.totals.percent_to_goal"
                unit="%"
                :tone="report.totals.percent_to_goal >= 100 ? 'success' : 'primary'"
            >
                <template #icon><TrendingUp /></template>
                <div
                    class="h-1.5 w-full overflow-hidden rounded-full bg-primary/15"
                    role="progressbar"
                    :aria-valuenow="Math.min(report.totals.percent_to_goal, 100)"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-label="Progress to annual goal"
                >
                    <div
                        class="h-full rounded-full transition-[width]"
                        :class="report.totals.percent_to_goal >= 100 ? 'bg-success' : 'bg-primary'"
                        :style="{ width: Math.min(report.totals.percent_to_goal, 100) + '%' }"
                    />
                </div>
            </StatCard>

            <StatCard
                :label="report.totals.variance <= 0 ? 'Ahead of target by' : 'Short of target by'"
                :value="number(Math.abs(report.totals.variance))"
                unit="FCFA"
                :tone="report.totals.variance <= 0 ? 'success' : 'warning'"
            >
                <template #icon><Scale /></template>
                <span :class="report.totals.variance <= 0 ? 'text-success' : 'text-warning'">
                    {{ report.totals.variance <= 0 ? 'Goal reached' : 'Still to earn this year' }}
                </span>
            </StatCard>
        </div>

        <div :class="table.wrapper">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-base font-semibold text-foreground">Breakdown by service</h2>
            </div>

            <div v-if="report.rows.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead :class="table.head">
                        <tr>
                            <th :class="[table.th, 'text-left']">Service</th>
                            <th :class="[table.th, 'text-right']">Current licences</th>
                            <th :class="[table.th, 'text-right']">Expected licences</th>
                            <th :class="[table.th, 'text-right']">Earned</th>
                            <th :class="[table.th, 'text-right']">Target</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="row in report.rows" :key="row.service_id" :class="table.row">
                            <td class="px-4 py-3 font-medium text-foreground">{{ row.service_name }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-foreground">{{ row.current_licenses }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-muted-foreground">{{ row.expected_licenses }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums text-foreground">
                                {{ formatMoney(row.actual_amount) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-muted-foreground">
                                {{ formatMoney(row.expected_amount) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t border-border bg-muted/40">
                        <tr>
                            <td class="px-4 py-3 font-semibold text-foreground">Total</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums text-foreground">{{ report.totals.current_licenses }}</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums text-muted-foreground">{{ report.totals.expected_licenses }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums text-foreground">{{ formatMoney(report.totals.actual_amount) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums text-muted-foreground">{{ formatMoney(report.totals.expected_amount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div v-else class="flex flex-col items-center justify-center px-4 py-16 text-center">
                <p class="text-sm text-muted-foreground">No budget set for {{ selectedYear }} yet.</p>
                <Link :href="budgetsRoutes.create.url({ query: { year: selectedYear } })" :class="[buttonVariants(), 'mt-4']">
                    Set up {{ selectedYear }} budget
                </Link>
            </div>
        </div>
    </div>
</template>
