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

interface Role {
    id: number | string;
    name: string;
}

const props = defineProps<{
    user: {
        id: number | string;
        name: string;
        email: string;
        created_at: string | null;
        updated_at: string | null;
        roles?: Array<{ id: number | string; name: string }>;
    };
    roles: Role[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edit User',
        description: 'Edit a user using the form below.',
        href: '/maintainers/users',
    },
];

const form = useForm('patch', route('maintainers.users.update', props.user.id), {
    id: props.user.id,
    name: props.user.name,
    email: props.user.email,
    password: '',
    created_at: props.user.created_at,
    updated_at: props.user.updated_at,
    roles: props.user.roles?.map((r) => Number(r.id)) || [],
});

const submit = () => {
    form.patch(route('maintainers.users.update', props.user.id), {
        preserveUrl: true,
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.log(errors);
        },
    });
};

const toggleRole = (roleId: number | string) => {
    const numericId = Number(roleId);
    const index = form.roles.indexOf(numericId);
    if (index > -1) {
        form.roles.splice(index, 1);
    } else {
        form.roles.push(numericId);
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
                    <Label for="user-id">ID</Label>
                    <Input id="user-id" v-model="form.id" type="text" class="mt-1 block w-full" readonly disabled />
                </div>
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" @change="form.validate('name')" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" @change="form.validate('email')" type="email" class="mt-1 block w-full" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" v-model="form.password" @change="form.validate('password')" type="password" class="mt-1 block w-full" />
                    <InputError :message="form.errors.password" />
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
                    <Label>Roles</Label>
                    <div class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="role in props.roles" :key="role.id" class="flex items-center space-x-2">
                            <Checkbox
                                :id="`role-${role.id}`"
                                :model-value="form.roles.includes(Number(role.id))"
                                @update:modelValue="toggleRole(role.id)"
                            />
                            <Label :for="`role-${role.id}`" class="text-sm font-normal">
                                {{ role.name }}
                            </Label>
                        </div>
                    </div>
                    <InputError :message="form.errors.roles" />
                </div>
                <!-- <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Update</Button>
                    </div> -->
                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="cursor-pointer">Update</Button>
                    <Link :href="route('maintainers.users.index')" class="text-sm underline"> Back to list </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
