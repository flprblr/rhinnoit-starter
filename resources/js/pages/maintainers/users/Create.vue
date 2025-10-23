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
    roles: Role[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create User',
        description: 'Create a user using the form below.',
        href: '/maintainers/users',
    },
];

const form = useForm('post', route('maintainers.users.store'), {
    name: '',
    email: '',
    password: '',
    roles: [] as (number | string)[],
});

const submit = () => {
    form.post(route('maintainers.users.store'), {
        preserveUrl: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            console.log(errors);
        },
    });
};

const toggleRole = (roleId: number | string) => {
    const index = form.roles.indexOf(roleId);
    if (index > -1) {
        form.roles.splice(index, 1);
    } else {
        form.roles.push(roleId);
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
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" @change="form.validate('email')" type="email" class="mt-1 block w-full" />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" v-model="form.password" @change="form.validate('password')" type="password" class="mt-1 block w-full" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label>Roles</Label>
                    <div class="grid grid-cols-1 gap-3 rounded-lg border p-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="role in props.roles" :key="role.id" class="flex items-center space-x-2">
                            <Checkbox
                                :id="`role-${role.id}`"
                                :model-value="form.roles.includes(Number(role.id))"
                                @update:model-value="toggleRole(role.id)"
                            />
                            <Label :for="`role-${role.id}`" class="text-sm font-normal">
                                {{ role.name }}
                            </Label>
                        </div>
                    </div>
                    <InputError :message="form.errors.roles" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="cursor-pointer">Create</Button>
                    <Link :href="route('maintainers.users.index')" class="text-sm underline"> Back to list </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
