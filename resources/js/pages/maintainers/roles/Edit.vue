<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue-inertia';

import { type BreadcrumbItem } from '@/types';

import { useFlashWatcher } from '@/composables/useFlashWatcher';

import AppLayout from '@/layouts/AppLayout.vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import HeadingSmall from '@/components/HeadingSmall.vue';

interface Permission {
    id: number | string;
    name: string;
}

const props = defineProps<{
    role: {
        id: number | string;
        name: string;
        created_at: string | null;
        updated_at: string | null;
        permissions?: Array<{ id: number | string; name: string }>;
    };
    permissions: Permission[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edit Role',
        description: 'Edit a role using the form below.',
        href: '/maintainers/roles',
    },
];

const form = useForm('patch', route('maintainers.roles.update', props.role.id), {
    id: props.role.id,
    name: props.role.name,
    created_at: props.role.created_at,
    updated_at: props.role.updated_at,
    permissions: props.role.permissions?.map((p) => Number(p.id)) || [],
});

const submit = () => {
    form.patch(route('maintainers.roles.update', props.role.id), {
        preserveUrl: true,
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.log(errors);
        },
    });
};

const togglePermission = (permissionId: number | string) => {
    const numericId = Number(permissionId);
    const index = form.permissions.indexOf(numericId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(numericId);
    }
};

useFlashWatcher();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="breadcrumbs[0].title" />

        <div class="max-w-xl space-y-3 p-4">
            <HeadingSmall :title="breadcrumbs[0].title" :description="breadcrumbs[0].description" />

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="role-id">ID</Label>
                    <Input id="role-id" v-model="form.id" type="text" class="mt-1 block w-full" readonly disabled />
                </div>
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" @change="form.validate('name')" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2" v-if="form.created_at">
                    <Label for="created_at">Created at</Label>
                    <Input id="created_at" v-model="form.created_at" type="text" class="mt-1 block w-full" readonly disabled />
                </div>

                <div class="grid gap-2" v-if="form.updated_at">
                    <Label for="updated_at">Updated at</Label>
                    <Input id="updated_at" v-model="form.updated_at" type="text" class="mt-1 block w-full" readonly disabled />
                </div>

                <div class="grid gap-2">
                    <Label>Permissions</Label>
                    <div class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="permission in props.permissions" :key="permission.id" class="flex items-center space-x-2">
                            <Checkbox
                                :id="`permission-${permission.id}`"
                                :model-value="form.permissions.includes(Number(permission.id))"
                                @update:modelValue="togglePermission(permission.id)"
                            />
                            <Label :for="`permission-${permission.id}`" class="text-sm font-normal">
                                {{ permission.name }}
                            </Label>
                        </div>
                    </div>
                    <InputError :message="form.errors.permissions" />
                </div>
                <!-- <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Update</Button>
                    </div> -->
                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="cursor-pointer">Update</Button>
                    <Link :href="route('maintainers.roles.index')" class="text-sm underline"> Back to list </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
