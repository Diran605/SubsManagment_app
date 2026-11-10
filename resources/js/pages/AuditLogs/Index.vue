<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import { index as auditLogsIndex } from '@/routes/audit_logs';

type Change = { field: string; old: string | null; new: string | null };

type Log = {
    id: number;
    event: string;
    type: string;
    type_label: string;
    label: string;
    user: string | null;
    created_at: string;
    changes: Change[];
};

const props = defineProps<{
    logs: {
        data: Log[];
        prev_page_url: string | null;
        next_page_url: string | null;
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: {
        type: string | null;
        event: string | null;
        user: number | null;
        date_from: string | null;
        date_to: string | null;
    };
    types: { value: string; label: string }[];
    events: string[];
    users: { id: number; name: string }[];
}>();

const filters = reactive({
    type: props.filters.type ?? '',
    event: props.filters.event ?? '',
    user: props.filters.user ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
});

watch(filters, () => {
    // Drop empty filters so the URL stays clean.
    const query = Object.fromEntries(
        Object.entries(filters).filter(
            ([, value]) => value !== '' && value !== null,
        ),
    );

    router.get(
        auditLogsIndex.url({ query }),
        {},
        { preserveState: true, replace: true },
    );
});

const clearFilters = () => {
    Object.assign(filters, {
        type: '',
        event: '',
        user: '',
        date_from: '',
        date_to: '',
    });
};

const eventLabels: Record<string, string> = {
    created: 'created',
    updated: 'updated',
    deleted: 'deleted',
    restored: 'restored',
    renewed: 'renewed',
    roles_changed: 'changed roles of',
};

const eventStyles: Record<string, string> = {
    created: 'bg-success/10 text-success ring-success/20',
    updated: 'bg-info/10 text-info ring-info/20',
    deleted: 'bg-destructive/10 text-destructive ring-destructive/20',
    restored: 'bg-warning/10 text-warning ring-warning/25',
    renewed: 'bg-success/10 text-success ring-success/20',
    roles_changed: 'bg-accent/10 text-accent ring-accent/20',
};

const formatEvent = (event: string) => event.replace('_', ' ');

const formatDateTime = (value: string) =>
    new Date(value).toLocaleString(undefined, {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const selectClass =
    'h-9 rounded-md border border-input bg-background px-3 text-sm text-foreground shadow-xs focus:border-ring focus:ring-2 focus:ring-ring/30 focus:outline-none';
</script>

<template>
    <Head title="Audit Logs" />

    <div class="flex h-full flex-col gap-6 p-4 md:p-8">
        <div>
            <h1
                class="text-foreground text-2xl font-semibold tracking-tight md:text-3xl"
            >
                Audit Logs
            </h1>
            <p class="text-muted-foreground mt-1 text-sm">
                Every change made in the app: who did it, when, and what
                changed.
            </p>
        </div>

        <!-- Filters -->
        <div
            class="border-border bg-card flex flex-wrap items-end gap-3 rounded-xl border p-4 shadow-xs"
        >
            <label
                class="text-muted-foreground flex flex-col gap-1 text-xs font-medium"
            >
                Record type
                <select v-model="filters.type" :class="selectClass">
                    <option value="">All types</option>
                    <option
                        v-for="t in props.types"
                        :key="t.value"
                        :value="t.value"
                    >
                        {{ t.label }}
                    </option>
                </select>
            </label>
            <label
                class="text-muted-foreground flex flex-col gap-1 text-xs font-medium"
            >
                Action
                <select v-model="filters.event" :class="selectClass">
                    <option value="">All actions</option>
                    <option
                        v-for="e in props.events"
                        :key="e"
                        :value="e"
                        class="capitalize"
                    >
                        {{ formatEvent(e) }}
                    </option>
                </select>
            </label>
            <label
                class="text-muted-foreground flex flex-col gap-1 text-xs font-medium"
            >
                Staff member
                <select v-model="filters.user" :class="selectClass">
                    <option value="">Everyone</option>
                    <option v-for="u in props.users" :key="u.id" :value="u.id">
                        {{ u.name }}
                    </option>
                </select>
            </label>
            <label
                class="text-muted-foreground flex flex-col gap-1 text-xs font-medium"
            >
                From
                <input
                    v-model="filters.date_from"
                    type="date"
                    :class="selectClass"
                />
            </label>
            <label
                class="text-muted-foreground flex flex-col gap-1 text-xs font-medium"
            >
                To
                <input
                    v-model="filters.date_to"
                    type="date"
                    :class="selectClass"
                />
            </label>
            <button
                type="button"
                class="text-muted-foreground hover:bg-muted hover:text-foreground h-9 rounded-md px-3 text-sm font-medium"
                @click="clearFilters"
            >
                Clear
            </button>
        </div>

        <!-- Log entries -->
        <div
            class="border-border bg-card overflow-hidden rounded-xl border shadow-xs"
        >
            <ul v-if="props.logs.data.length" class="divide-border divide-y">
                <li
                    v-for="log in props.logs.data"
                    :key="log.id"
                    class="px-5 py-4"
                >
                    <div
                        class="flex flex-wrap items-start justify-between gap-2"
                    >
                        <div
                            class="flex min-w-0 flex-wrap items-center gap-2 text-sm"
                        >
                            <span
                                class="rounded-md px-2 py-0.5 text-xs font-semibold capitalize ring-1 ring-inset"
                                :class="
                                    eventStyles[log.event] ??
                                    'bg-muted text-muted-foreground ring-border'
                                "
                            >
                                {{ formatEvent(log.event) }}
                            </span>
                            <span class="text-foreground font-medium">{{
                                log.user ?? 'System'
                            }}</span>
                            <span class="text-muted-foreground">{{
                                eventLabels[log.event] ?? log.event
                            }}</span>
                            <span class="text-muted-foreground"
                                >{{ log.type_label.toLowerCase() }}:</span
                            >
                            <span
                                class="text-foreground truncate font-medium"
                                >{{ log.label }}</span
                            >
                        </div>
                        <time
                            class="text-muted-foreground shrink-0 text-xs"
                            :datetime="log.created_at"
                        >
                            {{ formatDateTime(log.created_at) }}
                        </time>
                    </div>

                    <dl
                        v-if="log.changes.length"
                        class="bg-muted/50 mt-3 grid gap-1.5 rounded-lg p-3 text-xs"
                    >
                        <div
                            v-for="change in log.changes"
                            :key="change.field"
                            class="grid grid-cols-[8rem_1fr] gap-3"
                        >
                            <dt
                                class="text-muted-foreground font-medium capitalize"
                            >
                                {{ change.field }}
                            </dt>
                            <dd class="text-foreground min-w-0 break-words">
                                <template
                                    v-if="
                                        log.event === 'updated' ||
                                        log.event === 'roles_changed'
                                    "
                                >
                                    <span
                                        class="text-muted-foreground line-through"
                                        >{{ change.old ?? '—' }}</span
                                    >
                                    <span class="text-muted-foreground mx-1.5"
                                        >→</span
                                    >
                                </template>
                                <span>{{
                                    (log.event === 'deleted'
                                        ? change.old
                                        : change.new) ?? '—'
                                }}</span>
                            </dd>
                        </div>
                    </dl>
                </li>
            </ul>

            <div v-else class="px-5 py-16 text-center">
                <p class="text-foreground text-sm font-medium">
                    No activity found
                </p>
                <p class="text-muted-foreground mt-1 text-sm">
                    Try clearing the filters. Changes are recorded from the
                    moment the activity log was installed.
                </p>
            </div>

            <div
                v-if="props.logs.total > 0"
                class="border-border text-muted-foreground flex items-center justify-between border-t px-5 py-3 text-sm"
            >
                <span
                    >Showing {{ props.logs.from }}–{{ props.logs.to }} of
                    {{ props.logs.total }}</span
                >
                <div class="flex gap-2">
                    <Link
                        v-if="props.logs.prev_page_url"
                        :href="props.logs.prev_page_url"
                        preserve-scroll
                        class="border-border text-foreground hover:bg-muted rounded-md border px-3 py-1.5 font-medium"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="props.logs.next_page_url"
                        :href="props.logs.next_page_url"
                        preserve-scroll
                        class="border-border text-foreground hover:bg-muted rounded-md border px-3 py-1.5 font-medium"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
