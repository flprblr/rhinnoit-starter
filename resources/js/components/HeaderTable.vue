<script setup lang="ts">
import { computed } from 'vue';

import { Download, Plus, Upload } from 'lucide-vue-next';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';

defineOptions({ name: 'HeaderTable' });

type HeaderActionDefinition = {
    key: string;
    label: string;
    icon?: any;
    permission?: string;
    variant?: 'default' | 'outline' | 'secondary' | 'destructive' | 'ghost' | 'link';
};

const props = withDefaults(
  defineProps<{
    title: string;
    description?: string;
    actions?: Array<HeaderActionDefinition | string>;
    resource: string;
  }>(),
  {
    description: '',
    actions: () => [],
  },
);

const emit = defineEmits<{
    (e: string): void;
}>();

const defaultActions = [
    {
        key: 'create',
        label: 'Create',
        icon: Plus,
        permission: 'create',
        variant: 'outline',
    },
    {
        key: 'export',
        label: 'Export',
        icon: Download,
        permission: 'export',
        variant: 'outline',
    },
    {
        key: 'import',
        label: 'Import',
        icon: Upload,
        permission: 'import',
        variant: 'outline',
    },
];

const resolvedActions = computed<HeaderActionDefinition[]>(() => {
  if (!props.actions || props.actions.length === 0) {
    return defaultActions;
  }

  return props.actions.map((action) => {
    if (typeof action === 'string') {
      const preset = defaultActions.find((presetAction) => presetAction.key === action);
      return preset ?? { key: action, label: action, variant: 'outline' };
    }
    return {
      variant: 'outline',
      ...action,
    };
  });
});
</script>

<template>
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <HeadingSmall :title="props.title" :description="props.description" />
    <div class="flex gap-2">
      <slot name="actions">
        <Button
          v-for="action in resolvedActions"
          :key="action.key"
          :variant="action.variant ?? 'outline'"
          class="cursor-pointer"
          v-can="action.permission ? `${props.resource}.${action.permission}` : true"
          @click="emit(action.key)"
        >
          <component
            v-if="action.icon"
            :is="action.icon"
            class="mr-2 h-4 w-4"
          />
          {{ action.label }}
        </Button>
      </slot>
    </div>
  </div>
</template>
