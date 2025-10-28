<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();

const themes = [
    { value: 'light', label: 'Light', icon: Sun },
    { value: 'dark', label: 'Dark', icon: Moon },
    { value: 'system', label: 'System', icon: Monitor },
];

const getCurrentIcon = () => {
    // Si el tema es 'system', necesitamos detectar el tema real del sistema
    if (appearance.value === 'system') {
        if (typeof window !== 'undefined') {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            return mediaQuery.matches ? Moon : Sun;
        }
        return Sun; // fallback por defecto
    }

    // Para 'light' y 'dark', usar el ícono correspondiente
    return appearance.value === 'dark' ? Moon : Sun;
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="cursor-pointer transition-colors">
                <component :is="getCurrentIcon()" class="h-5 w-5" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem
                v-for="theme in themes"
                :key="theme.value"
                @click="updateAppearance(theme.value as 'light' | 'dark' | 'system')"
                class="cursor-pointer">
                <component :is="theme.icon" class="mr-2 h-4 w-4" />
                {{ theme.label }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
