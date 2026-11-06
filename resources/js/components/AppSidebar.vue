<script setup lang="ts">
import businessesRoutes from '@/routes/businesses';
import servicesRoutes from '@/routes/services';
import subscriptionsRoutes from '@/routes/subscriptions';
import reportsRoutes from '@/routes/reports';
import budgetsRoutes from '@/routes/budgets';
import auditLogsRoutes from '@/routes/audit_logs';
import staffRoutes from '@/routes/staff';
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
    PiggyBank,
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
            href: dashboard.url(),
            icon: LayoutGrid,
        },
    ];

    const perms = page.props.auth?.user?.permissions || [];
    
    if (perms.includes('view businesses')) {
        items.push({ title: 'Businesses', href: businessesRoutes.index.url(), icon: Building2 });
    }
    if (perms.includes('view services')) {
        items.push({ title: 'Services', href: servicesRoutes.index.url(), icon: Briefcase });
    }
    if (perms.includes('view subscriptions')) {
        items.push({ title: 'Subscriptions', href: subscriptionsRoutes.index.url(), icon: BookOpen });
    }
    if (perms.includes('view reports')) {
        items.push({ title: 'Reports', href: reportsRoutes.index.url(), icon: BarChart3 });
        items.push({ title: 'Budget', href: budgetsRoutes.index.url(), icon: PiggyBank });
    }
    
    // Must match the route middleware in routes/web.php.
    if (perms.includes('manage settings')) {
        items.push({ title: 'Audit Logs', href: auditLogsRoutes.index.url(), icon: Activity });
    }

    // Staff routes are restricted to the Admin role (routes/web.php).
    if (page.props.auth?.user?.roles?.includes('Admin')) {
        items.push({
            title: 'Staff Accounts',
            href: staffRoutes.index.url(),
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
