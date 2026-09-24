<script setup lang="ts">
import { route } from 'ziggy-js';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    FolderGit2,
    LayoutGrid,
    Building2,
    Briefcase,
    Users,
    BarChart3,
    Activity,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage<any>();

const mainNavItems = computed(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: route('dashboard'),
            icon: LayoutGrid,
        },
    ];

    const perms = page.props.auth?.user?.permissions || [];
    
    if (perms.includes('view businesses')) {
        items.push({ title: 'Businesses', href: route('businesses.index'), icon: Building2 });
    }
    if (perms.includes('view services')) {
        items.push({ title: 'Services', href: route('services.index'), icon: Briefcase });
    }
    if (perms.includes('view subscriptions')) {
        items.push({ title: 'Subscriptions', href: route('subscriptions.index'), icon: BookOpen });
    }
    if (perms.includes('view reports')) {
        items.push({ title: 'Reports', href: route('reports.index'), icon: BarChart3 });
    }
    
    // Audit logs for Admin only or settings manager
    if (page.props.auth?.user?.roles?.includes('Admin') || perms.includes('manage settings')) {
        items.push({ title: 'Audit Logs', href: route('audit_logs.index'), icon: Activity });
    }

    if (perms.includes('manage staff') || page.props.auth?.user?.roles?.includes('Admin')) {
        items.push({
            title: 'Staff Accounts',
            href: route('staff.index'),
            icon: Users,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
