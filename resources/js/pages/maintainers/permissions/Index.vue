<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import { type BreadcrumbItem, type RowAction, type TableColumn } from '@/types';
import { Eye, SquarePen, Trash2 } from 'lucide-vue-next';

import HeadingSmall from '@/components/HeadingSmall.vue';
import SimpleTable from '@/components/SimpleTable.vue';
import { Button } from '@/components/ui/button';
import {
    create as createPermission,
    destroy as destroyPermission,
    edit as editPermission,
    exportMethod as exportPermissions,
    index as permissionsIndex,
    show as showPermission,
} from '@/routes/maintainers/permissions';
import { form as importPermissionsForm } from '@/routes/maintainers/permissions/import';

const props = defineProps<{
    permissions: TablePaginator;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List Permissions',
        description: 'List permissions in the table below.',
        href: permissionsIndex().url,
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
    {
        key: 'show',
        label: 'Show',
        icon: Eye,
        can: 'permissions.show',
        type: 'emit',
    },
    {
        key: 'edit',
        label: 'Edit',
        icon: SquarePen,
        can: 'permissions.edit',
        type: 'emit',
    },
    {
        key: 'delete',
        label: 'Delete',
        icon: Trash2,
        can: 'permissions.destroy',
        type: 'emit',
        confirm: {
            title: '¿Estás seguro?',
            description:
                'Esto eliminará permanentemente el permiso seleccionado.',
        },
    },
];

const onRowAction = ({ key, id }: { key: string; id: number | string }) => {
    if (key === 'show') {
        router.visit(showPermission(Number(id)).url);
        return;
    }
    if (key === 'edit') {
        router.visit(editPermission(Number(id)).url);
        return;
    }
    if (key === 'delete') {
        router.visit(destroyPermission(Number(id)).url, { method: 'delete' });
        return;
    }
};

const goCreate = () => {
    router.visit(createPermission().url);
};

const goImport = () => {
    router.visit(importPermissionsForm().url);
};

const downloadExport = () => {
    const link = document.createElement('a');
    link.href = exportPermissions().url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const onChangePage = (p: number) => {
    router.get(permissionsIndex.url({ mergeQuery: { page: p } }), {
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
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <HeadingSmall
                    :title="breadcrumbs[0].title"
                    :description="breadcrumbs[0].description"
                />
                <div class="flex gap-2">
                    <Button
                        v-if="headerActions.includes('create')"
                        variant="outline"
                        class="cursor-pointer"
                        v-can="'permissions.create'"
                        @click="goCreate"
                    >
                        Create
                    </Button>

                    <Button
                        v-if="headerActions.includes('export')"
                        variant="outline"
                        class="cursor-pointer"
                        v-can="'permissions.export'"
                        @click="downloadExport"
                    >
                        Export
                    </Button>

                    <Button
                        v-if="headerActions.includes('import')"
                        variant="outline"
                        class="cursor-pointer"
                        v-can="'permissions.import'"
                        @click="goImport"
                    >
                        Import
                    </Button>
                </div>
            </div>
            <SimpleTable
                :columns="columns"
                :items="props.permissions.data"
                :items-per-page="props.permissions.per_page || 10"
                :total="props.permissions.total || 0"
                :current-page="props.permissions.current_page || 1"
                row-key="id"
                actions-label="Action"
                :row-actions="rowActions"
                @row:action="onRowAction"
                @update:page="onChangePage"
            />
        </div>
    </AppLayout>
</template>
