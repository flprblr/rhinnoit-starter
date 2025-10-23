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
    permissions: Permission[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create Role',
        description: 'Create a role using the form below.',
        href: '/maintainers/roles',
    },
];

const form = useForm('post', route('maintainers.roles.store'), {
    name: '',
    permissions: [] as (number | string)[],
});

const submit = () => {
    form.post(route('maintainers.roles.store'), {
        preserveUrl: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            console.log(errors);
        },
    });
};

const togglePermission = (permissionId: number | string) => {
    const index = form.permissions.indexOf(permissionId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionId);
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
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" @change="form.validate('name')" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label>Permissions</Label>
                    <div class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="permission in props.permissions" :key="permission.id" class="flex items-center space-x-2">
                            <Checkbox
                                :id="`permission-${permission.id}`"
                                :model-value="form.permissions.includes(Number(permission.id))"
                                @update:model-value="togglePermission(permission.id)"
                            />
                            <Label :for="`permission-${permission.id}`" class="text-sm font-normal">
                                {{ permission.name }}
                            </Label>
                        </div>
                    </div>
                    <InputError :message="form.errors.permissions" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="cursor-pointer">Create</Button>
                    <Link :href="route('maintainers.roles.index')" class="text-sm underline"> Back to list </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
