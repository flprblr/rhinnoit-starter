import { ref } from 'vue';

type CrudActionPayload<T> = {
    key: string;
    id: number | string;
    item: T;
};

type CrudActionHandlers<T> = Record<string, (payload: CrudActionPayload<T>) => void>;

export type UseCrudTableOptions<T> = {
    onCreateOpen?: () => void;
    onImportOpen?: () => void;
    onShowOpen?: (item: T) => void;
    onEditOpen?: (item: T) => void;
    onDelete?: (payload: CrudActionPayload<T>) => void;
    onAction?: CrudActionHandlers<T>;
};

export const useCrudTable = <T>(options: UseCrudTableOptions<T> = {}) => {
    const isCreateOpen = ref(false);
    const isEditOpen = ref(false);
    const isShowOpen = ref(false);
    const isImportOpen = ref(false);
    const selectedItem = ref<T | null>(null);

    const openCreate = () => {
        isCreateOpen.value = true;
        options.onCreateOpen?.();
    };

    const openImport = () => {
        isImportOpen.value = true;
        options.onImportOpen?.();
    };

    const closeCreate = () => {
        isCreateOpen.value = false;
    };

    const closeEdit = () => {
        isEditOpen.value = false;
    };

    const closeShow = () => {
        isShowOpen.value = false;
    };

    const closeImport = () => {
        isImportOpen.value = false;
    };

    const handleRowAction = (payload: CrudActionPayload<T>) => {
        if (payload.key === 'show') {
            selectedItem.value = payload.item;
            options.onShowOpen?.(payload.item);
            isShowOpen.value = true;
            return;
        }

        if (payload.key === 'edit') {
            selectedItem.value = payload.item;
            options.onEditOpen?.(payload.item);
            isEditOpen.value = true;
            return;
        }

        if (payload.key === 'delete') {
            options.onDelete?.(payload);
            return;
        }

        const customAction = options.onAction?.[payload.key];
        if (customAction) {
            customAction(payload);
        }
    };

    return {
        isCreateOpen,
        isEditOpen,
        isShowOpen,
        isImportOpen,
        selectedItem,
        openCreate,
        openImport,
        closeCreate,
        closeEdit,
        closeShow,
        closeImport,
        handleRowAction,
    };
};
