<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import maintainers from '@/routes/maintainers';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import {
    LogOut,
    Settings,
    UserRound,
    UserRoundCheck,
    UserRoundCog,
} from 'lucide-vue-next';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="edit()" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true" v-if="$can('users.index')">
            <Link
                class="block w-full"
                :href="maintainers.users.index().url"
                prefetch
                as="button"
            >
                <UserRound class="mr-2 h-4 w-4" />
                Users
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true" v-if="$can('roles.index')">
            <Link
                class="block w-full"
                :href="maintainers.roles.index().url"
                prefetch
                as="button"
            >
                <UserRoundCog class="mr-2 h-4 w-4" />
                Roles
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true" v-if="$can('permissions.index')">
            <Link
                class="block w-full"
                :href="maintainers.permissions.index().url"
                prefetch
                as="button"
            >
                <UserRoundCheck class="mr-2 h-4 w-4" />
                Permissions
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
