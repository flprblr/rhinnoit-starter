<script setup lang="ts">
defineOptions({ name: 'HeaderTable' });
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Download, Plus, Upload } from 'lucide-vue-next';

type HeaderAction = 'create' | 'export' | 'import';

const props = withDefaults(
    defineProps<{
        title: string;
        description?: string;
        actions: ReadonlyArray<HeaderAction>;
        resource: string; // e.g., 'users'
    }>(),
    {
        description: '',
    },
);

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'export'): void;
    (e: 'import'): void;
}>();
</script>

<template>
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <HeadingSmall :title="props.title" :description="props.description" />
        <div class="flex gap-2">
            <Button
                v-if="props.actions.includes('create')"
                variant="outline"
                class="cursor-pointer"
                v-can="`${props.resource}.create`"
                @click="emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                Create
            </Button>

            <Button
                v-if="props.actions.includes('export')"
                variant="outline"
                class="cursor-pointer"
                v-can="`${props.resource}.export`"
                @click="emit('export')">
                <Download class="mr-2 h-4 w-4" />
                Export
            </Button>

            <Button
                v-if="props.actions.includes('import')"
                variant="outline"
                class="cursor-pointer"
                v-can="`${props.resource}.import`"
                @click="emit('import')">
                <Upload class="mr-2 h-4 w-4" />
                Import
            </Button>
        </div>
    </div>
</template>
