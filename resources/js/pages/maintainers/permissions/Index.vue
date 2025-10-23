<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import { type BreadcrumbItem, type RowAction, type TableColumn } from '@/types';
import { Eye, SquarePen, Trash2 } from 'lucide-vue-next';

import HeaderTable from '@/components/HeaderTable.vue';
import SimpleTable from '@/components/SimpleTable.vue';

const props = defineProps<{
    permissions: TablePaginator;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List Permissions',
        description: 'List permissions in the table below.',
        href: '/maintainers/permissions',
    },
];

const columns: TableColumn[] = [
    { label: 'ID', field: 'id' },
    { label: 'Name', field: 'name' },
    { label: 'Created At', field: 'created_at' },
    { label: 'Updated At', field: 'updated_at' },
];

const headerActions = ['create', 'export', 'import'] as const;

const rowActions: RowAction[] = [
    { key: 'show', label: 'Show', icon: Eye, can: 'permissions.show', type: 'route', route: 'maintainers.permissions.show', paramFrom: 'id' },
    { key: 'edit', label: 'Edit', icon: SquarePen, can: 'permissions.edit', type: 'route', route: 'maintainers.permissions.edit', paramFrom: 'id' },
    {
        key: 'delete',
        label: 'Delete',
        icon: Trash2,
        can: 'permissions.destroy',
        type: 'route',
        route: 'maintainers.permissions.destroy',
        paramFrom: 'id',
        method: 'delete',
        confirm: { title: '¿Estás seguro?', description: 'Esto eliminará permanentemente el permiso seleccionado.' },
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="breadcrumbs[0].title" />
        <div class="space-y-3 p-4">
            <HeaderTable :title="breadcrumbs[0].title" :description="breadcrumbs[0].description" :actions="headerActions" resource="permissions" />
            <SimpleTable
                :columns="columns"
                :items="props.permissions.data"
                :items-per-page="props.permissions.per_page || 10"
                :total="props.permissions.total || 0"
                :current-page="props.permissions.current_page || 1"
                index-route="maintainers.permissions.index"
                row-key="id"
                actions-label="Action"
                :row-actions="rowActions"
            />
        </div>
    </AppLayout>
</template>
