import { computed, ref, watch } from 'vue';

import { router } from '@inertiajs/vue3';

type RouteQueryOptions = {
    mergeQuery?: Record<string, string | number | boolean | undefined>;
};

export type UseTableActionsOptions = {
    indexRoute: {
        url: (options?: RouteQueryOptions) => string;
    };
    exportRoute?: () => { url: string };
    filters?: {
        search?: string | null;
    };
    pagination: PaginatedCollection<Record<string, unknown>>;
    loading: { value: boolean };
};

export function useTableActions(options: UseTableActionsOptions) {
    const { indexRoute, exportRoute, filters, pagination, loading } = options;

    const searchTerm = ref(filters?.search ?? '');
    const currentPerPage = computed(() => pagination.meta?.per_page ?? 10);

    watch(
        () => filters?.search ?? '',
        (value) => {
            if (value !== searchTerm.value) {
                searchTerm.value = value;
            }
        },
    );

    const onChangePage = (page: number) => {
        loading.value = true;
        router.get(
            indexRoute.url({
                mergeQuery: {
                    page,
                    per_page: currentPerPage.value,
                    search: searchTerm.value || undefined,
                },
            }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => {
                    loading.value = false;
                },
            },
        );
    };

    const onChangePerPage = (perPage: number) => {
        loading.value = true;
        router.get(
            indexRoute.url({
                mergeQuery: {
                    page: 1,
                    per_page: perPage,
                    search: searchTerm.value || undefined,
                },
            }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => {
                    loading.value = false;
                },
            },
        );
    };

    const onSearchChange = (value: string) => {
        searchTerm.value = value;
        loading.value = true;
        router.get(
            indexRoute.url({
                mergeQuery: {
                    page: 1,
                    per_page: currentPerPage.value,
                    search: value || undefined,
                },
            }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => {
                    loading.value = false;
                },
            },
        );
    };

    const downloadExport = () => {
        if (!exportRoute) {
            return;
        }

        const link = document.createElement('a');
        link.href = exportRoute().url;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    return {
        searchTerm,
        currentPerPage,
        onChangePage,
        onChangePerPage,
        onSearchChange,
        downloadExport,
    };
}

