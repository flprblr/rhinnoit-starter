<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import { type BreadcrumbItem, type RowAction, type TableColumn } from '@/types';
import { Eye, SquarePen, Trash2 } from 'lucide-vue-next';

import HeaderTable from '@/components/HeaderTable.vue';
import SimpleTable from '@/components/SimpleTable.vue';
import {
    create as createRole,
    destroy as destroyRole,
    edit as editRole,
    exportMethod as exportRoles,
    index as rolesIndex,
    show as showRole,
} from '@/routes/maintainers/roles';
import { form as importRolesForm } from '@/routes/maintainers/roles/import';

const props = defineProps<{
    roles: TablePaginator;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List Roles',
        description: 'List roles in the table below.',
        href: rolesIndex().url,
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
    { key: 'show', label: 'Show', icon: Eye, can: 'roles.show' },
    {
        key: 'edit',
        label: 'Edit',
        icon: SquarePen,
        can: 'roles.edit',
    },
    {
        key: 'delete',
        label: 'Delete',
        icon: Trash2,
        can: 'roles.destroy',
        confirm: {
            title: '¿Estás seguro?',
            description: 'Esto eliminará permanentemente el rol seleccionado.',
        },
    },
];

const onRowAction = ({ key, id }: { key: string; id: number | string }) => {
    if (key === 'show') {
        router.visit(showRole(Number(id)).url);
        return;
    }
    if (key === 'edit') {
        router.visit(editRole(Number(id)).url);
        return;
    }
    if (key === 'delete') {
        router.visit(destroyRole(Number(id)).url, { method: 'delete' });
        return;
    }
};

const goCreate = () => {
    router.visit(createRole().url);
};

const goImport = () => {
    router.visit(importRolesForm().url);
};

const downloadExport = () => {
    const link = document.createElement('a');
    link.href = exportRoles().url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const onChangePage = (p: number) => {
    router.get(rolesIndex.url({ mergeQuery: { page: p } }), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="breadcrumbs[0].title" />
        <div class="space-y-3 p-4">
            <HeaderTable
                :title="breadcrumbs[0].title"
                :description="breadcrumbs[0].description"
                :actions="headerActions"
                resource="roles"
                @create="goCreate"
                @export="downloadExport"
                @import="goImport"
            />

            <SimpleTable
                :columns="columns"
                :items="props.roles.data"
                :items-per-page="props.roles.per_page || 10"
                :total="props.roles.total || 0"
                :current-page="props.roles.current_page || 1"
                row-key="id"
                actions-label="Action"
                :row-actions="rowActions"
                @row:action="onRowAction"
                @update:page="onChangePage"
            />
        </div>
    </AppLayout>
</template>
