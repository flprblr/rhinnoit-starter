<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import HeadingSmall from '@/components/HeadingSmall.vue';

const props = defineProps<{
    user: {
        id: number | string;
        name: string;
        email: string;
        created_at: string | null;
        updated_at: string | null;
        roles?: Array<{ id: number | string; name: string }>;
    };
    roles: Array<{ id: number | string; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Show User',
        description: 'Show a user using the form below.',
        href: '/maintainers/users',
    },
];

const form = useForm({
    id: props.user.id,
    name: props.user.name,
    email: props.user.email,
    created_at: props.user.created_at,
    updated_at: props.user.updated_at,
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="breadcrumbs[0].title" />

        <div class="max-w-xl space-y-3 p-4">
            <HeadingSmall :title="breadcrumbs[0].title" :description="breadcrumbs[0].description" />

            <form class="space-y-6">
                <div class="grid gap-2">
                    <Label for="user-id">ID</Label>
                    <Input id="user-id" v-model="form.id" type="text" class="mt-1 block w-full" readonly disabled />
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" type="text" class="mt-1 block w-full" readonly disabled />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" class="mt-1 block w-full" readonly disabled />
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
                                :model-value="props.user.roles?.some((r) => Number(r.id) === Number(role.id))"
                                disabled
                            />
                            <Label :for="`role-${role.id}`" class="text-sm font-normal">
                                {{ role.name }}
                            </Label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Link :href="route('maintainers.users.edit', props.user.id)">
                        <Button size="sm" type="button" class="cursor-pointer">Edit</Button>
                    </Link>
                    <Link :href="route('maintainers.users.index')" class="text-sm underline"> Back to list </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
