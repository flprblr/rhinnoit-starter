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
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useFlashWatcher } from '@/composables/useFlashWatcher';
import {
    destroy as destroyUser,
    exportMethod as exportUsers,
    store as storeUser,
    update as updateUser,
    index as usersIndex,
} from '@/routes/maintainers/users';
import { form as importUsersForm } from '@/routes/maintainers/users/import';

interface Role {
    id: number | string;
    name: string;
}

const props = defineProps<{
    users: TablePaginator;
    roles: Role[];
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
    {
        key: 'show',
        label: 'Show',
        icon: Eye,
        can: 'users.show',
    },
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
            title: 'Are you sure?',
            description: 'This will permanently delete the selected user.',
        },
    },
];

// Dialog states
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isShowOpen = ref(false);
const isImportOpen = ref(false);
type UserRow = {
    id: number | string;
    name: string;
    email: string;
    created_at?: string | null;
    updated_at?: string | null;
    roles?: Array<{ id: number | string; name: string }>;
};

const selectedUser = ref<UserRow | null>(null);

// Forms
const createForm = useForm('post', storeUser().url, {
    name: '',
    email: '',
    password: '',
    roles: [] as (number | string)[],
});

const editForm = useForm('patch', '', {
    id: '',
    name: '',
    email: '',
    password: '',
    created_at: null as string | null,
    updated_at: null as string | null,
    roles: [] as number[],
});

const importForm = useForm('post', importUsersForm().url, {
    file: null as File | null,
});

// Actions
const onRowAction = ({ key, id }: { key: string; id: number | string }) => {
    const user = props.users.data.find((u: Record<string, unknown>) => Number(u.id as number) === Number(id)) as UserRow | undefined;

    if (!user) return;

    if (key === 'show') {
        selectedUser.value = user;
        isShowOpen.value = true;
        return;
    }
    if (key === 'edit') {
        selectedUser.value = user;
        editForm.id = String(user.id);
        editForm.name = user.name;
        editForm.email = String(user.email);
        editForm.password = '';
        editForm.created_at = user.created_at ?? null;
        editForm.updated_at = user.updated_at ?? null;
        editForm.roles = user.roles?.map((r) => Number(r.id)) || [];
        isEditOpen.value = true;
        return;
    }
    if (key === 'delete') {
        router.visit(destroyUser(Number(id)).url, { method: 'delete' });
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

// Form submissions
const submitCreate = () => {
    createForm.post(storeUser().url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isCreateOpen.value = false;
            createForm.reset();
        },
    });
};

const submitEdit = () => {
    editForm.patch(updateUser(Number(editForm.id)).url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isEditOpen.value = false;
            editForm.reset();
        },
    });
};

const toggleCreateRole = (roleId: number | string) => {
    const index = createForm.roles.indexOf(roleId);
    if (index > -1) {
        createForm.roles.splice(index, 1);
    } else {
        createForm.roles.push(roleId);
    }
};

const toggleEditRole = (roleId: number | string) => {
    const numericId = Number(roleId);
    const index = editForm.roles.indexOf(numericId);
    if (index > -1) {
        editForm.roles.splice(index, 1);
    } else {
        editForm.roles.push(numericId);
    }
};

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    importForm.file = file;
    importForm.validate('file');
};

const submitImport = () => {
    importForm.post(importUsersForm().url, {
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
                resource="users"
                @create="goCreate"
                @export="downloadExport"
                @import="goImport" />

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
                @update:page="onChangePage" />
        </div>

        <!-- Create Dialog -->
        <Dialog v-model:open="isCreateOpen">
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>Create User</DialogTitle>
                    <DialogDescription>Create a new user for the system.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCreate" class="space-y-4" autocomplete="off">
                    <div class="grid gap-2">
                        <Label for="create-name">Name</Label>
                        <Input id="create-name" v-model="createForm.name" @change="createForm.validate('name')" type="text" />
                        <InputError :message="createForm.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-email">Email</Label>
                        <Input
                            id="create-email"
                            v-model="createForm.email"
                            @change="createForm.validate('email')"
                            type="email"
                            name="new-email"
                            autocomplete="off" />
                        <InputError :message="createForm.errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-password">Password</Label>
                        <Input
                            id="create-password"
                            v-model="createForm.password"
                            @change="createForm.validate('password')"
                            type="password"
                            name="new-password"
                            autocomplete="new-password" />
                        <InputError :message="createForm.errors.password" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Roles</Label>
                        <div class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3">
                            <div v-for="role in props.roles" :key="role.id" class="flex items-center space-x-2">
                                <Checkbox
                                    :id="`create-role-${role.id}`"
                                    :model-value="createForm.roles.includes(Number(role.id))"
                                    @update:model-value="toggleCreateRole(role.id)" />
                                <Label :for="`create-role-${role.id}`" class="text-sm font-normal">
                                    {{ role.name }}
                                </Label>
                            </div>
                        </div>
                        <InputError :message="createForm.errors.roles" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isCreateOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="createForm.processing" class="cursor-pointer">Create</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>Edit User</DialogTitle>
                    <DialogDescription>Update the user information.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEdit" class="space-y-4" autocomplete="off">
                    <div class="grid gap-2">
                        <Label for="edit-id">ID</Label>
                        <Input id="edit-id" v-model="editForm.id" type="text" readonly disabled />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-name">Name</Label>
                        <Input id="edit-name" v-model="editForm.name" @change="editForm.validate('name')" type="text" />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-email">Email</Label>
                        <Input
                            id="edit-email"
                            v-model="editForm.email"
                            @change="editForm.validate('email')"
                            type="email"
                            name="edit-email"
                            autocomplete="off" />
                        <InputError :message="editForm.errors.email" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-password">Password</Label>
                        <Input
                            id="edit-password"
                            v-model="editForm.password"
                            @change="editForm.validate('password')"
                            type="password"
                            name="edit-password"
                            autocomplete="new-password"
                            placeholder="Leave empty to keep current" />
                        <InputError :message="editForm.errors.password" />
                    </div>
                    <div class="grid gap-2" v-if="editForm.created_at">
                        <Label for="edit-created">Created At</Label>
                        <Input id="edit-created" v-model="editForm.created_at" type="text" readonly disabled />
                    </div>
                    <div class="grid gap-2" v-if="editForm.updated_at">
                        <Label for="edit-updated">Updated At</Label>
                        <Input id="edit-updated" v-model="editForm.updated_at" type="text" readonly disabled />
                    </div>
                    <div class="grid gap-2">
                        <Label>Roles</Label>
                        <div class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3">
                            <div v-for="role in props.roles" :key="role.id" class="flex items-center space-x-2">
                                <Checkbox
                                    :id="`edit-role-${role.id}`"
                                    :model-value="editForm.roles.includes(Number(role.id))"
                                    @update:model-value="toggleEditRole(role.id)" />
                                <Label :for="`edit-role-${role.id}`" class="text-sm font-normal">
                                    {{ role.name }}
                                </Label>
                            </div>
                        </div>
                        <InputError :message="editForm.errors.roles" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isEditOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="editForm.processing" class="cursor-pointer">Update</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Show Dialog -->
        <Dialog v-model:open="isShowOpen">
            <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>User Details</DialogTitle>
                    <DialogDescription>View user information.</DialogDescription>
                </DialogHeader>
                <div class="space-y-4" v-if="selectedUser">
                    <div class="grid gap-2">
                        <Label>ID</Label>
                        <Input :model-value="selectedUser.id" type="text" readonly disabled />
                    </div>
                    <div class="grid gap-2">
                        <Label>Name</Label>
                        <Input :model-value="selectedUser.name" type="text" readonly disabled />
                    </div>
                    <div class="grid gap-2">
                        <Label>Email</Label>
                        <Input :model-value="selectedUser.email" type="email" readonly disabled />
                    </div>
                    <div class="grid gap-2" v-if="selectedUser.created_at">
                        <Label>Created At</Label>
                        <Input :model-value="selectedUser.created_at" type="text" readonly disabled />
                    </div>
                    <div class="grid gap-2" v-if="selectedUser.updated_at">
                        <Label>Updated At</Label>
                        <Input :model-value="selectedUser.updated_at" type="text" readonly disabled />
                    </div>
                    <div class="grid gap-2">
                        <Label>Roles</Label>
                        <div class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3">
                            <div v-for="role in props.roles" :key="role.id" class="flex items-center space-x-2">
                                <Checkbox
                                    :id="`show-role-${role.id}`"
                                    :model-value="selectedUser.roles?.some((r: { id: number | string }) => Number(r.id) === Number(role.id))"
                                    disabled />
                                <Label :for="`show-role-${role.id}`" class="text-sm font-normal">
                                    {{ role.name }}
                                </Label>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="isShowOpen = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Import Dialog -->
        <Dialog v-model:open="isImportOpen">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Import Users</DialogTitle>
                    <DialogDescription>Upload an Excel file to import users.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitImport" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="import-file">File</Label>
                        <Input id="import-file" type="file" accept=".xlsx" @change="onFileChange" />
                        <InputError :message="importForm.errors.file" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isImportOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="importForm.processing" class="cursor-pointer">Import</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
