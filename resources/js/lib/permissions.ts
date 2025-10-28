import { usePage } from '@inertiajs/vue3';

import type { AppPageProps, User } from '@/types';

export type Can = (permission: string | string[]) => boolean;

export const createCan = (): Can => {
    const page = usePage<AppPageProps>();
    // NOTE: page.props may update; read fresh each call
    return (permission: string | string[]): boolean => {
        const authUser = page.props?.auth?.user as
            | (User & {
                  permissions?: string[];
              })
            | null;
        const userPermissions: string[] = Array.isArray(authUser?.permissions) ? (authUser?.permissions as string[]) : [];

        if (Array.isArray(permission)) {
            return permission.every((perm) => userPermissions.includes(perm));
        }

        return userPermissions.includes(permission);
    };
};

// Singleton helper for convenient imports when DI not needed
let cachedCan: Can | null = null;
export const can: Can = ((perm: string | string[]) => {
    if (!cachedCan) cachedCan = createCan();
    return cachedCan(perm);
}) as Can;
