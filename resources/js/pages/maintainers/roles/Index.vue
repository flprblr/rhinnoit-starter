<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import { type BreadcrumbItem, type RowAction, type TableColumn } from '@/types';
import { Eye, SquarePen, Trash2 } from 'lucide-vue-next';

import HeaderTable from '@/components/HeaderTable.vue';
import SimpleTable from '@/components/SimpleTable.vue';

const props = defineProps<{
    roles: TablePaginator;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List Roles',
        description: 'List roles in the table below.',
        href: '/maintainers/roles',
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
    { key: 'show', label: 'Show', icon: Eye, can: 'roles.show', type: 'route', route: 'maintainers.roles.show', paramFrom: 'id' },
    { key: 'edit', label: 'Edit', icon: SquarePen, can: 'roles.edit', type: 'route', route: 'maintainers.roles.edit', paramFrom: 'id' },
    {
        key: 'delete',
        label: 'Delete',
        icon: Trash2,
        can: 'roles.destroy',
        type: 'route',
        route: 'maintainers.roles.destroy',
        paramFrom: 'id',
        method: 'delete',
        confirm: { title: '¿Estás seguro?', description: 'Esto eliminará permanentemente el rol seleccionado.' },
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="breadcrumbs[0].title" />
        <div class="space-y-3 p-4">
            <HeaderTable :title="breadcrumbs[0].title" :description="breadcrumbs[0].description" :actions="headerActions" resource="roles" />
            <SimpleTable
                :columns="columns"
                :items="props.roles.data"
                :items-per-page="props.roles.per_page || 10"
                :total="props.roles.total || 0"
                :current-page="props.roles.current_page || 1"
                index-route="maintainers.roles.index"
                row-key="id"
                actions-label="Action"
                :row-actions="rowActions"
            />
        </div>
    </AppLayout>
</template>
