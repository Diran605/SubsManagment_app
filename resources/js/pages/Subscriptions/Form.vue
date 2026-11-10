<script setup lang="ts">
import subscriptionsRoutes from '@/routes/subscriptions';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import { Info, Smartphone } from '@lucide/vue';
import FormField from '@/components/FormField.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatMoney } from '@/lib/format';
import { card, selectClass, textareaClass } from '@/lib/ui';
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

const title = isEditing
    ? 'Edit subscription'
    : isRenewing
      ? 'Renew subscription'
      : 'New subscription';

const description = isEditing
    ? 'Change the details of this subscription.'
    : isRenewing
      ? `Start a new period for ${props.renewing.business?.name ?? 'this client'}. Client, service and mode stay the same.`
      : 'Record a new subscription for a client.';

const backHref = isEditing
    ? subscriptionsRoutes.show.url(props.subscription.id)
    : isRenewing
      ? subscriptionsRoutes.show.url(props.renewing.id)
      : subscriptionsRoutes.index.url();

const packageLabel = (pkg: any) => {
    if (!pkg.price) {
        return `${pkg.name} (free)`;
    }

    const unit =
        pkg.pricing_model === 'per_student' ? 'per student' : 'per month';

    return `${pkg.name} (${formatMoney(pkg.price)} FCFA ${unit})`;
};

const amountHint = computed(() => {
    if (!selectedPackage.value) {
        return 'Enter the agreed contract amount.';
    }

    return isPerStudent.value
        ? 'Calculated: students × price per student.'
        : 'Calculated: monthly price × duration.';
});

const checkboxClass =
    'border-input text-primary focus-visible:ring-ring/50 mt-0.5 size-4 rounded border focus-visible:ring-2';
</script>

<template>
    <Head :title="title" />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-8">
        <PageHeader
            :title="title"
            :description="description"
            :back-href="backHref"
            :back-label="
                isEditing || isRenewing
                    ? 'Back to subscription'
                    : 'Back to subscriptions'
            "
        />

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Client & service -->
            <section :class="card.base">
                <div :class="card.header">
                    <h2 :class="card.title">Client and service</h2>
                </div>
                <div
                    :class="[
                        card.body,
                        'grid grid-cols-1 gap-5 md:grid-cols-2',
                    ]"
                >
                    <FormField
                        label="Business"
                        for="business_id"
                        required
                        :error="form.errors.business_id"
                    >
                        <select
                            id="business_id"
                            v-model="form.business_id"
                            required
                            :disabled="isRenewing"
                            :class="selectClass"
                        >
                            <option value="">Select a business</option>
                            <option
                                v-for="b in props.businesses"
                                :key="b.id"
                                :value="b.id"
                            >
                                {{ b.name }}
                            </option>
                        </select>
                    </FormField>

                    <FormField
                        label="Service"
                        for="service_id"
                        required
                        :error="form.errors.service_id"
                    >
                        <select
                            id="service_id"
                            v-model="form.service_id"
                            required
                            :disabled="isRenewing"
                            :class="selectClass"
                        >
                            <option value="">Select a service</option>
                            <option
                                v-for="s in props.services"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                    </FormField>

                    <FormField
                        label="Mode"
                        for="mode"
                        required
                        :error="form.errors.mode"
                    >
                        <select
                            id="mode"
                            v-model="form.mode"
                            required
                            :disabled="isRenewing"
                            :class="[selectClass, 'capitalize']"
                        >
                            <option value="">Select a mode</option>
                            <option
                                v-for="m in availableModes"
                                :key="m"
                                :value="m"
                            >
                                {{ m }}
                            </option>
                        </select>
                    </FormField>
                </div>
            </section>

            <!-- Package & modules -->
            <section
                v-if="
                    form.service_id &&
                    (availablePackages.length > 0 ||
                        availableModules.length > 0)
                "
                :class="card.base"
            >
                <div :class="card.header">
                    <h2 :class="card.title">Package and modules</h2>
                </div>
                <div :class="[card.body, 'space-y-5']">
                    <template v-if="availablePackages.length > 0">
                        <FormField
                            label="Package"
                            for="package_id"
                            hint="Optional. Picking a package fills in the price and its modules."
                            :error="form.errors.package_id"
                        >
                            <select
                                id="package_id"
                                v-model="form.package_id"
                                :class="selectClass"
                            >
                                <option value="">
                                    No package (custom amount)
                                </option>
                                <option
                                    v-for="pkg in availablePackages"
                                    :key="pkg.id"
                                    :value="pkg.id"
                                >
                                    {{ packageLabel(pkg) }}
                                </option>
                            </select>
                        </FormField>

                        <FormField
                            v-if="isPerStudent"
                            label="Enrolled students"
                            for="student_count"
                            required
                            :hint="`Used to work out the contract value (${formatMoney(selectedPackage?.price)} FCFA per student).`"
                            :error="form.errors.student_count"
                        >
                            <Input
                                id="student_count"
                                v-model.number="form.student_count"
                                type="number"
                                min="1"
                                required
                                placeholder="e.g. 850"
                            />
                        </FormField>

                        <div
                            v-if="isStudentMoMo"
                            class="border-warning/30 bg-warning/5 text-foreground flex gap-3 rounded-lg border p-4 text-sm"
                        >
                            <Smartphone
                                class="text-warning mt-0.5 size-4 shrink-0"
                            />
                            <p>
                                <strong>Paid by students via MoMo.</strong> The
                                amount is a collection target. Log payments on
                                the subscription page as they arrive; only
                                collected money counts as revenue.
                            </p>
                        </div>
                    </template>

                    <div v-if="availableModules.length > 0" class="space-y-2">
                        <p class="text-foreground text-sm font-medium">
                            Included modules
                        </p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label
                                v-for="mod in availableModules"
                                :key="mod.id"
                                class="border-border hover:bg-muted/40 has-[:checked]:border-primary/40 has-[:checked]:bg-primary/5 flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-colors"
                            >
                                <input
                                    type="checkbox"
                                    :value="mod.id"
                                    v-model="form.modules"
                                    :class="checkboxClass"
                                />
                                <span class="flex flex-col">
                                    <span
                                        class="text-foreground text-sm font-medium"
                                        >{{ mod.name }}</span
                                    >
                                    <span
                                        v-if="mod.description"
                                        class="text-muted-foreground text-xs"
                                        >{{ mod.description }}</span
                                    >
                                </span>
                            </label>
                        </div>
                        <p
                            v-if="form.errors.modules"
                            class="text-destructive text-sm"
                        >
                            {{ form.errors.modules }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Period & price -->
            <section :class="card.base">
                <div :class="card.header">
                    <h2 :class="card.title">Period and price</h2>
                </div>
                <div :class="[card.body, 'space-y-5']">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        <FormField
                            label="Start date"
                            for="start_date"
                            required
                            :error="form.errors.start_date"
                        >
                            <Input
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                required
                            />
                        </FormField>
                        <FormField
                            label="Duration (months)"
                            for="duration"
                            required
                        >
                            <Input
                                id="duration"
                                v-model="duration"
                                type="number"
                                min="1"
                                step="1"
                                required
                            />
                        </FormField>
                        <FormField
                            label="End date"
                            for="end_date"
                            hint="Last day covered, worked out for you."
                            :error="form.errors.end_date"
                        >
                            <Input
                                id="end_date"
                                v-model="form.end_date"
                                type="date"
                                required
                                readonly
                                tabindex="-1"
                                class="bg-muted/60 text-muted-foreground cursor-default"
                            />
                        </FormField>
                    </div>

                    <FormField
                        :label="
                            isStudentMoMo
                                ? 'Collection target (FCFA)'
                                : 'Amount (FCFA)'
                        "
                        for="amount"
                        required
                        :hint="amountHint"
                        :error="form.errors.amount"
                        class="md:max-w-xs"
                    >
                        <Input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            :readonly="!!selectedPackage"
                            :class="
                                selectedPackage
                                    ? 'bg-muted/60 cursor-default font-medium'
                                    : ''
                            "
                        />
                    </FormField>
                </div>
            </section>

            <!-- Notes -->
            <section :class="card.base">
                <div :class="card.body">
                    <FormField
                        label="Notes"
                        for="notes"
                        :error="form.errors.notes"
                    >
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            :class="textareaClass"
                        ></textarea>
                    </FormField>
                </div>
            </section>

            <div
                v-if="isRenewing"
                class="text-muted-foreground flex items-center gap-2 text-xs"
            >
                <Info class="size-4 shrink-0" />
                The current subscription is kept as history and marked as
                renewed.
            </div>

            <div class="flex justify-end gap-2">
                <Link
                    :href="backHref"
                    :class="buttonVariants({ variant: 'outline' })"
                >
                    Cancel
                </Link>
                <Button type="submit" :disabled="form.processing">
                    {{
                        isEditing
                            ? 'Save changes'
                            : isRenewing
                              ? 'Confirm renewal'
                              : 'Save subscription'
                    }}
                </Button>
            </div>
        </form>
    </div>
</template>
