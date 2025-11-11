export {};

declare global {
    type PaginationLink = {
        url: string | null;
        label: string;
        page: number | null;
        active: boolean;
    };

    type PaginationMeta = {
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };

    type PaginatedCollection<T = Record<string, unknown>> = {
        data: T[];
        meta: PaginationMeta;
        links: PaginationLink[];
    };
}
