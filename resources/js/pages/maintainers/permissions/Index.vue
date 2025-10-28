<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue-inertia';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';

import { type BreadcrumbItem, type RowAction, type TableColumn } from '@/types';
import { Eye, SquarePen, Trash2 } from 'lucide-vue-next';

import HeaderTable from '@/components/HeaderTable.vue';
import InputError from '@/components/InputError.vue';
import SimpleTable from '@/components/SimpleTable.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useFlashWatcher } from '@/composables/useFlashWatcher';
import {
    destroy as destroyPermission,
    exportMethod as exportPermissions,
    index as permissionsIndex,
    store as storePermission,
    update as updatePermission,
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
    },
    {
        key: 'edit',
        label: 'Edit',
        icon: SquarePen,
        can: 'permissions.edit',
    },
    {
        key: 'delete',
        label: 'Delete',
        icon: Trash2,
        can: 'permissions.destroy',
        confirm: {
            title: 'Are you sure?',
            description:
                'This will permanently delete the selected permission.',
        },
    },
];

// Dialog states
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isShowOpen = ref(false);
const isImportOpen = ref(false);
type PermissionRow = { id: number | string; name: string };
const selectedPermission = ref<PermissionRow | null>(null);

// Forms
const createForm = useForm('post', storePermission().url, {
    name: '',
});

const editForm = useForm('patch', '', {
    id: '',
    name: '',
    created_at: null as string | null,
    updated_at: null as string | null,
});

const importForm = useForm('post', importPermissionsForm().url, {
    file: null as File | null,
});

// Actions
const onRowAction = ({ key, id }: { key: string; id: number | string }) => {
    const permission = props.permissions.data.find(
        (p: Record<string, unknown>) => Number(p.id as number) === Number(id),
    );

    if (!permission) return;

    if (key === 'show') {
        selectedPermission.value = permission;
        isShowOpen.value = true;
        return;
    }
    if (key === 'edit') {
        selectedPermission.value = permission;
        editForm.id = permission.id;
        editForm.name = permission.name;
        editForm.created_at = permission.created_at;
        editForm.updated_at = permission.updated_at;
        isEditOpen.value = true;
        return;
    }
    if (key === 'delete') {
        router.visit(destroyPermission(Number(id)).url, { method: 'delete' });
        return;
    }
};

const goCreate = () => {
    createForm.reset();
    isCreateOpen.value = true;
};

const goImport = () => {
    importForm.reset();
    isImportOpen.value = true;
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

// Form submissions
const submitCreate = () => {
    createForm.post(storePermission().url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isCreateOpen.value = false;
            createForm.reset();
        },
    });
};

const submitEdit = () => {
    editForm.patch(updatePermission(Number(editForm.id)).url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isEditOpen.value = false;
            editForm.reset();
        },
    });
};

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    importForm.file = file;
    importForm.validate('file');
};

const submitImport = () => {
    importForm.post(importPermissionsForm().url, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isImportOpen.value = false;
            importForm.reset();
        },
    });
};

useFlashWatcher();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="breadcrumbs[0].title" />
        <div class="space-y-3 p-4">
            <HeaderTable
                :title="breadcrumbs[0].title"
                :description="breadcrumbs[0].description"
                :actions="headerActions"
                resource="permissions"
                @create="goCreate"
                @export="downloadExport"
                @import="goImport"
            />

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

        <!-- Create Dialog -->
        <Dialog v-model:open="isCreateOpen">
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>Create Permission</DialogTitle>
                    <DialogDescription>
                        Create a new permission for the system.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="create-name">Name</Label>
                        <Input
                            id="create-name"
                            v-model="createForm.name"
                            @change="createForm.validate('name')"
                            type="text"
                        />
                        <InputError :message="createForm.errors.name" />
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="isCreateOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="createForm.processing"
                            class="cursor-pointer"
                        >
                            Create
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent
                class="max-h-[90vh] overflow-y-auto sm:max-w-[600px]"
            >
                <DialogHeader>
                    <DialogTitle>Edit Permission</DialogTitle>
                    <DialogDescription>
                        Update the permission information.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="edit-id">ID</Label>
                        <Input
                            id="edit-id"
                            v-model="editForm.id"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-name">Name</Label>
                        <Input
                            id="edit-name"
                            v-model="editForm.name"
                            @change="editForm.validate('name')"
                            type="text"
                        />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <div class="grid gap-2" v-if="editForm.created_at">
                        <Label for="edit-created">Created At</Label>
                        <Input
                            id="edit-created"
                            v-model="editForm.created_at"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div class="grid gap-2" v-if="editForm.updated_at">
                        <Label for="edit-updated">Updated At</Label>
                        <Input
                            id="edit-updated"
                            v-model="editForm.updated_at"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="isEditOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="editForm.processing"
                            class="cursor-pointer"
                        >
                            Update
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Show Dialog -->
        <Dialog v-model:open="isShowOpen">
            <DialogContent
                class="max-h-[90vh] overflow-y-auto sm:max-w-[600px]"
            >
                <DialogHeader>
                    <DialogTitle>Permission Details</DialogTitle>
                    <DialogDescription>
                        View permission information.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4" v-if="selectedPermission">
                    <div class="grid gap-2">
                        <Label>ID</Label>
                        <Input
                            :model-value="selectedPermission.id"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label>Name</Label>
                        <Input
                            :model-value="selectedPermission.name"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div
                        class="grid gap-2"
                        v-if="selectedPermission.created_at"
                    >
                        <Label>Created At</Label>
                        <Input
                            :model-value="selectedPermission.created_at"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div
                        class="grid gap-2"
                        v-if="selectedPermission.updated_at"
                    >
                        <Label>Updated At</Label>
                        <Input
                            :model-value="selectedPermission.updated_at"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="isShowOpen = false"
                    >
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Import Dialog -->
        <Dialog v-model:open="isImportOpen">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Import Permissions</DialogTitle>
                    <DialogDescription>
                        Upload an Excel file to import permissions.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitImport" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="import-file">File</Label>
                        <Input
                            id="import-file"
                            type="file"
                            accept=".xlsx"
                            @change="onFileChange"
                        />
                        <InputError :message="importForm.errors.file" />
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="isImportOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="importForm.processing"
                            class="cursor-pointer"
                        >
                            Import
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
