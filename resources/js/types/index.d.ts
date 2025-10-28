import { InertiaLinkProps } from '@inertiajs/vue3';

import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    description?: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    flash?: Flash;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    dni?: string | null;
    phone?: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export type TableColumn<Row = Record<string, unknown>> = {
    label: string;
    field: keyof Row | string;
    class?: string;
    formatter?: (value: unknown, row: Row) => unknown;
};

export type RowAction = {
    key: 'show' | 'edit' | 'delete' | string;
    label: string;
    can?: string;
    icon?: Component;
    confirm?: { title: string; description: string };
};

export interface Flash {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}
