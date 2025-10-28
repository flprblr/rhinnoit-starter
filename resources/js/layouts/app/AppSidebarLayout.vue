<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const toasterTheme = ref<'light' | 'dark'>('light');

let observer: MutationObserver | null = null;

const updateThemeFromDom = () => {
    const isDark = document.documentElement.classList.contains('dark');
    toasterTheme.value = isDark ? 'dark' : 'light';
};

onMounted(() => {
    updateThemeFromDom();
    observer = new MutationObserver(updateThemeFromDom);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <Toaster :theme="toasterTheme" />
    </AppShell>
</template>
