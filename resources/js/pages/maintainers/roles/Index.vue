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
import { Checkbox } from '@/components/ui/checkbox';
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
    destroy as destroyRole,
    exportMethod as exportRoles,
    index as rolesIndex,
    store as storeRole,
    update as updateRole,
} from '@/routes/maintainers/roles';
import { form as importRolesForm } from '@/routes/maintainers/roles/import';

interface Permission {
    id: number | string;
    name: string;
}

const props = defineProps<{
    roles: TablePaginator;
    permissions: Permission[];
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
    {
        key: 'show',
        label: 'Show',
        icon: Eye,
        can: 'roles.show',
    },
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
            title: 'Are you sure?',
            description: 'This will permanently delete the selected role.',
        },
    },
];

// Dialog states
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isShowOpen = ref(false);
const isImportOpen = ref(false);
type RoleRow = {
    id: number | string;
    name: string;
    created_at?: string | null;
    updated_at?: string | null;
    permissions?: Array<{ id: number | string; name: string }>;
};

const selectedRole = ref<RoleRow | null>(null);

// Forms
const createForm = useForm('post', storeRole().url, {
    name: '',
    permissions: [] as (number | string)[],
});

const editForm = useForm('patch', '', {
    id: '',
    name: '',
    created_at: null as string | null,
    updated_at: null as string | null,
    permissions: [] as number[],
});

const importForm = useForm('post', importRolesForm().url, {
    file: null as File | null,
});

// Actions
const onRowAction = ({ key, id }: { key: string; id: number | string }) => {
    const role = props.roles.data.find(
        (r: Record<string, unknown>) => Number(r.id as number) === Number(id),
    ) as RoleRow | undefined;

    if (!role) return;

    if (key === 'show') {
        selectedRole.value = role;
        isShowOpen.value = true;
        return;
    }
    if (key === 'edit') {
        selectedRole.value = role;
        editForm.id = String(role.id);
        editForm.name = String(role.name);
        editForm.created_at = role.created_at ?? null;
        editForm.updated_at = role.updated_at ?? null;
        editForm.permissions = role.permissions?.map((p) => Number(p.id)) || [];
        isEditOpen.value = true;
        return;
    }
    if (key === 'delete') {
        router.visit(destroyRole(Number(id)).url, { method: 'delete' });
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

// Form submissions
const submitCreate = () => {
    createForm.post(storeRole().url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isCreateOpen.value = false;
            createForm.reset();
        },
    });
};

const submitEdit = () => {
    editForm.patch(updateRole(Number(editForm.id)).url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isEditOpen.value = false;
            editForm.reset();
        },
    });
};

const toggleCreatePermission = (permissionId: number | string) => {
    const index = createForm.permissions.indexOf(permissionId);
    if (index > -1) {
        createForm.permissions.splice(index, 1);
    } else {
        createForm.permissions.push(permissionId);
    }
};

const toggleEditPermission = (permissionId: number | string) => {
    const numericId = Number(permissionId);
    const index = editForm.permissions.indexOf(numericId);
    if (index > -1) {
        editForm.permissions.splice(index, 1);
    } else {
        editForm.permissions.push(numericId);
    }
};

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    importForm.file = file;
    importForm.validate('file');
};

const submitImport = () => {
    importForm.post(importRolesForm().url, {
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

        <!-- Create Dialog -->
        <Dialog v-model:open="isCreateOpen">
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>Create Role</DialogTitle>
                    <DialogDescription>
                        Create a new role for the system.
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
                    <div class="grid gap-2">
                        <Label>Permissions</Label>
                        <div
                            class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="permission in props.permissions"
                                :key="permission.id"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="`create-permission-${permission.id}`"
                                    :model-value="
                                        createForm.permissions.includes(
                                            Number(permission.id),
                                        )
                                    "
                                    @update:model-value="
                                        toggleCreatePermission(permission.id)
                                    "
                                />
                                <Label
                                    :for="`create-permission-${permission.id}`"
                                    class="text-sm font-normal"
                                >
                                    {{ permission.name }}
                                </Label>
                            </div>
                        </div>
                        <InputError :message="createForm.errors.permissions" />
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
                    <DialogTitle>Edit Role</DialogTitle>
                    <DialogDescription>
                        Update the role information.
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
                    <div class="grid gap-2">
                        <Label>Permissions</Label>
                        <div
                            class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="permission in props.permissions"
                                :key="permission.id"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="`edit-permission-${permission.id}`"
                                    :model-value="
                                        editForm.permissions.includes(
                                            Number(permission.id),
                                        )
                                    "
                                    @update:model-value="
                                        toggleEditPermission(permission.id)
                                    "
                                />
                                <Label
                                    :for="`edit-permission-${permission.id}`"
                                    class="text-sm font-normal"
                                >
                                    {{ permission.name }}
                                </Label>
                            </div>
                        </div>
                        <InputError :message="editForm.errors.permissions" />
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
                    <DialogTitle>Role Details</DialogTitle>
                    <DialogDescription>
                        View role information.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4" v-if="selectedRole">
                    <div class="grid gap-2">
                        <Label>ID</Label>
                        <Input
                            :model-value="selectedRole.id"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label>Name</Label>
                        <Input
                            :model-value="selectedRole.name"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div class="grid gap-2" v-if="selectedRole.created_at">
                        <Label>Created At</Label>
                        <Input
                            :model-value="selectedRole.created_at"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div class="grid gap-2" v-if="selectedRole.updated_at">
                        <Label>Updated At</Label>
                        <Input
                            :model-value="selectedRole.updated_at"
                            type="text"
                            readonly
                            disabled
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label>Permissions</Label>
                        <div
                            class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="permission in props.permissions"
                                :key="permission.id"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="`show-permission-${permission.id}`"
                                    :model-value="
                                        selectedRole.permissions?.some(
                                            (p: any) =>
                                                Number(p.id) ===
                                                Number(permission.id),
                                        )
                                    "
                                    disabled
                                />
                                <Label
                                    :for="`show-permission-${permission.id}`"
                                    class="text-sm font-normal"
                                >
                                    {{ permission.name }}
                                </Label>
                            </div>
                        </div>
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
                    <DialogTitle>Import Roles</DialogTitle>
                    <DialogDescription>
                        Upload an Excel file to import roles.
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
