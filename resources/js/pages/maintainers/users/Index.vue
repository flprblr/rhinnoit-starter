<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import { type BreadcrumbItem, type RowAction, type TableColumn } from '@/types';
import { Eye, SquarePen, Trash2 } from 'lucide-vue-next';

import HeaderTable from '@/components/HeaderTable.vue';
import SimpleTable from '@/components/SimpleTable.vue';
import {
    create as createUser,
    destroy as destroyUser,
    edit as editUser,
    exportMethod as exportUsers,
    show as showUser,
    index as usersIndex,
} from '@/routes/maintainers/users';
import { form as importUsersForm } from '@/routes/maintainers/users/import';

const props = defineProps<{
    users: TablePaginator;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List Users',
        description: 'List users in the table below.',
        href: usersIndex().url,
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
    { key: 'show', label: 'Show', icon: Eye, can: 'users.show' },
    {
        key: 'edit',
        label: 'Edit',
        icon: SquarePen,
        can: 'users.edit',
    },
    {
        key: 'delete',
        label: 'Delete',
        icon: Trash2,
        can: 'users.destroy',
        confirm: {
            title: '¿Estás seguro?',
            description:
                'Esto eliminará permanentemente el usuario seleccionado.',
        },
    },
];

const onRowAction = ({ key, id }: { key: string; id: number | string }) => {
    if (key === 'show') {
        router.visit(showUser(Number(id)).url);
        return;
    }
    if (key === 'edit') {
        router.visit(editUser(Number(id)).url);
        return;
    }
    if (key === 'delete') {
        router.visit(destroyUser(Number(id)).url, { method: 'delete' });
        return;
    }
};

const goCreate = () => {
    router.visit(createUser().url);
};

const goImport = () => {
    router.visit(importUsersForm().url);
};

const downloadExport = () => {
    const link = document.createElement('a');
    link.href = exportUsers().url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const onChangePage = (p: number) => {
    router.get(usersIndex.url({ mergeQuery: { page: p } }), {
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
                resource="users"
                @create="goCreate"
                @export="downloadExport"
                @import="goImport"
            />

            <SimpleTable
                :columns="columns"
                :items="props.users.data"
                :items-per-page="props.users.per_page || 10"
                :total="props.users.total || 0"
                :current-page="props.users.current_page || 1"
                row-key="id"
                actions-label="Action"
                :row-actions="rowActions"
                @row:action="onRowAction"
                @update:page="onChangePage"
            />
        </div>
    </AppLayout>
</template>
