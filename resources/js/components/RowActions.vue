<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MoreHorizontal } from '@lucide/vue';
import type { Component } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export type RowAction = {
    label: string;
    icon?: Component;
    /** Navigate to this URL... */
    href?: string;
    /** ...with this HTTP method (e.g. 'patch' for a toggle). */
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    /** ...or run this instead. */
    onSelect?: () => void;
    /** Danger items are red and placed last, after a divider. */
    danger?: boolean;
    hidden?: boolean;
};

const props = defineProps<{ actions: RowAction[]; label?: string }>();

const visible = () => props.actions.filter((a) => !a.hidden);
const normal = () => visible().filter((a) => !a.danger);
const danger = () => visible().filter((a) => a.danger);
</script>

<template>
    <!-- @click.stop so opening the menu doesn't also trigger the row's click. -->
    <div v-if="visible().length" class="flex justify-end" @click.stop>
        <DropdownMenu :modal="false">
            <DropdownMenuTrigger
                class="text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:ring-ring/50 data-[state=open]:bg-muted data-[state=open]:text-foreground inline-flex size-8 items-center justify-center rounded-md transition-colors outline-none focus-visible:ring-2"
                :aria-label="label ?? 'Actions'"
            >
                <MoreHorizontal class="size-4" />
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-44">
                <template v-for="action in normal()" :key="action.label">
                    <DropdownMenuItem v-if="action.href" as-child>
                        <Link
                            :href="action.href"
                            :method="action.method ?? 'get'"
                            :as="
                                action.method && action.method !== 'get'
                                    ? 'button'
                                    : 'a'
                            "
                            class="w-full cursor-pointer"
                        >
                            <component :is="action.icon" v-if="action.icon" />
                            {{ action.label }}
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        v-else
                        class="cursor-pointer"
                        @select="action.onSelect?.()"
                    >
                        <component :is="action.icon" v-if="action.icon" />
                        {{ action.label }}
                    </DropdownMenuItem>
                </template>
                <template v-if="danger().length">
                    <DropdownMenuSeparator v-if="normal().length" />
                    <DropdownMenuItem
                        v-for="action in danger()"
                        :key="action.label"
                        variant="destructive"
                        class="cursor-pointer"
                        @select="action.onSelect?.()"
                    >
                        <component :is="action.icon" v-if="action.icon" />
                        {{ action.label }}
                    </DropdownMenuItem>
                </template>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
