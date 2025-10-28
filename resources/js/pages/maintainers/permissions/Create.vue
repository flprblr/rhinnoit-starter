<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue-inertia';

import { type BreadcrumbItem } from '@/types';

import { useFlashWatcher } from '@/composables/useFlashWatcher';

import AppLayout from '@/layouts/AppLayout.vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import HeadingSmall from '@/components/HeadingSmall.vue';
import {
    index as permissionsIndex,
    store as storePermission,
} from '@/routes/maintainers/permissions';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create Permission',
        description: 'Create a permission using the form below.',
        href: permissionsIndex().url,
    },
];

const form = useForm('post', storePermission().url, {
    name: '',
});

const submit = () => {
    form.post(storePermission().url, {
        preserveUrl: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => form.reset(),
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
            <HeadingSmall
                :title="breadcrumbs[0].title"
                :description="breadcrumbs[0].description"
            />
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        @change="form.validate('name')"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="cursor-pointer"
                        >Create</Button
                    >
                    <Link
                        :href="permissionsIndex().url"
                        class="text-sm underline"
                    >
                        Back to list
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
