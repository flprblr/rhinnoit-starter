<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { Toaster } from '@/components/ui/sonner';
import { usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;

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
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
        <Toaster :theme="toasterTheme" />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
        <Toaster :theme="toasterTheme" />
    </SidebarProvider>
</template>
