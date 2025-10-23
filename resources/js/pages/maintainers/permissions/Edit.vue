<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue-inertia';

import { type BreadcrumbItem } from '@/types';

import { useFlashWatcher } from '@/composables/useFlashWatcher';

import AppLayout from '@/layouts/AppLayout.vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    permission: {
        id: number | string;
        name: string;
        created_at: string | null;
        updated_at: string | null;
        roles?: Array<{ id: number | string; name: string }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edit Permission',
        description: 'Edit a permission using the form below.',
        href: '/maintainers/permissions',
    },
];

const form = useForm('patch', route('maintainers.permissions.update', props.permission.id), {
    id: props.permission.id,
    name: props.permission.name,
    created_at: props.permission.created_at,
    updated_at: props.permission.updated_at,
});

const submit = () => {
    form.patch(route('maintainers.permissions.update', props.permission.id), {
        preserveUrl: true,
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.log(errors);
        },
    });
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
                    <Label for="permission-id">ID</Label>
                    <Input id="permission-id" v-model="form.id" type="text" class="mt-1 block w-full" readonly disabled />
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

                <div class="grid gap-2" v-if="props.permission.roles && props.permission.roles.length">
                    <Label>Roles with: {{ props.permission.name }}</Label>
                    <div class="flex flex-wrap gap-2">
                        <Button size="sm" v-for="role in props.permission.roles" :key="role.id" variant="outline"> {{ role.name }} </Button>
                    </div>
                </div>
                <!-- <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Update</Button>
                    </div> -->
                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="cursor-pointer">Update</Button>
                    <Link :href="route('maintainers.permissions.index')" class="text-sm underline"> Back to list </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
