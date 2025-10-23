<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import { type BreadcrumbItem, type RowAction, type TableColumn } from '@/types';
import { Eye, SquarePen, Trash2 } from 'lucide-vue-next';

import HeaderTable from '@/components/HeaderTable.vue';
import SimpleTable from '@/components/SimpleTable.vue';

const props = defineProps<{
    users: TablePaginator;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List Users',
        description: 'List users in the table below.',
        href: '/maintainers/users',
    },
];

const columns: TableColumn[] = [
    { label: 'ID', field: 'id' },
    { label: 'Name', field: 'name' },
    { label: 'Email', field: 'email' },
    { label: 'Created At', field: 'created_at' },
    { label: 'Updated At', field: 'updated_at' },
];

const headerActions = ['create', 'export', 'import'] as const;

const rowActions: RowAction[] = [
    { key: 'show', label: 'Show', icon: Eye, can: 'users.show', type: 'route', route: 'maintainers.users.show', paramFrom: 'id' },
    { key: 'edit', label: 'Edit', icon: SquarePen, can: 'users.edit', type: 'route', route: 'maintainers.users.edit', paramFrom: 'id' },
    {
        key: 'delete',
        label: 'Delete',
        icon: Trash2,
        can: 'users.destroy',
        type: 'route',
        route: 'maintainers.users.destroy',
        paramFrom: 'id',
        method: 'delete',
        confirm: { title: '¿Estás seguro?', description: 'Esto eliminará permanentemente el usuario seleccionado.' },
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="breadcrumbs[0].title" />
        <div class="space-y-3 p-4">
            <HeaderTable :title="breadcrumbs[0].title" :description="breadcrumbs[0].description" :actions="headerActions" resource="users" />
            <SimpleTable
                :columns="columns"
                :items="props.users.data"
                :items-per-page="props.users.per_page || 10"
                :total="props.users.total || 0"
                :current-page="props.users.current_page || 1"
                index-route="maintainers.users.index"
                row-key="id"
                actions-label="Action"
                :row-actions="rowActions"
            />
        </div>
    </AppLayout>
</template>
