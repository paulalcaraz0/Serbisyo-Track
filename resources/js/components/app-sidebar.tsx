import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BarChart3, ClipboardList, LayoutGrid, LibraryBig, ScrollText, Settings2, Users } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Requests',
        url: '/staff/requests',
        icon: ClipboardList,
    },
];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const navigation =
        auth.user?.role === 'administrator'
            ? [
                  ...mainNavItems,
                  { title: 'Reports', url: '/admin/reports', icon: BarChart3 },
                  { title: 'Staff', url: '/admin/staff', icon: Users },
                  { title: 'Services', url: '/admin/services', icon: LibraryBig },
                  { title: 'Audit history', url: '/admin/audit-events', icon: ScrollText },
                  { title: 'Office settings', url: '/admin/settings', icon: Settings2 },
              ]
            : mainNavItems;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navigation} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
