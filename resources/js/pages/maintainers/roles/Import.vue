<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue-inertia';

import { useFlashWatcher } from '@/composables/useFlashWatcher';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Import Roles',
        description: 'Import roles using the form below.',
        href: '/maintainers/roles',
    },
];

const form = useForm('post', route('maintainers.roles.import'), {
    file: null as File | null,
});

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    form.file = file;

    form.validate('file');
};

const submit = () => {
    form.post(route('maintainers.roles.import'), {
        forceFormData: true,
        preserveUrl: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => form.reset('file'),
        onError: (errors) => console.log(errors),
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
                    <Label for="file">File</Label>
                    <Input id="file" type="file" accept=".xlsx" class="mt-1 block w-full" @change="onFileChange" />
                    <InputError :message="form.errors.file" />
                </div>
                <!-- <div class="flex items-center gap-4">
                        <Button :disabled="form.processing" class="cursor-pointer">Import</Button>
                    </div> -->
                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="cursor-pointer">Import</Button>
                    <Link :href="route('maintainers.roles.index')" class="text-sm underline"> Back to list </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
