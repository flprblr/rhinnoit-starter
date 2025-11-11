<script setup lang="ts">
import { computed, ref } from 'vue';

import type { AcceptableValue } from 'reka-ui';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { RowAction, TableColumn } from '@/types';

defineOptions({ name: 'DataTable' });

const props = withDefaults(
    defineProps<{
        columns: TableColumn<Record<string, unknown>>[];
        pagination: PaginatedCollection<Record<string, unknown>>;
        rowKey?: string;
        actionsLabel?: string;
        showActions?: boolean;
        rowActions?: RowAction[];
        loading?: boolean;
        emptyState?: string;
        cellClass?: string;
        perPageOptions?: number[];
        perPageLabel?: string;
    }>(),
    {
        rowKey: 'id',
        showActions: true,
        loading: false,
        emptyState: 'No entries found.',
        cellClass: 'whitespace-nowrap',
    },
);

const emit = defineEmits<{
    (e: 'update:page', value: number): void;
    (e: 'update:perPage', value: number): void;
    (
        e: 'row:action',
        payload: {
            key: string;
            id: string | number;
            item: Record<string, unknown>;
        },
    ): void;
}>();

const isConfirmOpen = ref(false);
const pendingAction = ref<{
    action: RowAction;
    item: Record<string, unknown>;
} | null>(null);

const getCellValue = (item: Record<string, unknown>, field: string | number | symbol) => {
    const key = String(field);
    return (item as Record<string, unknown>)[key];
};

const handleActionClick = (action: RowAction, item: Record<string, unknown>) => {
    if (action.confirm) {
        pendingAction.value = { action, item };
        isConfirmOpen.value = true;
        return;
    }
    emit('row:action', {
        key: action.key,
        id: (item as Record<string, unknown>)[props.rowKey as string] as string | number,
        item,
    });
};

const confirmAction = () => {
    if (pendingAction.value) {
        const { action, item } = pendingAction.value;
        const id = (item as Record<string, unknown>)[props.rowKey as string] as string | number;
        emit('row:action', { key: action.key, id, item });
    }
    isConfirmOpen.value = false;
    pendingAction.value = null;
};

const cancelAction = () => {
    isConfirmOpen.value = false;
    pendingAction.value = null;
};

const rows = computed(() => props.pagination?.data ?? []);

const paginationMeta = computed(() => {
    const meta = props.pagination?.meta;
    if (!meta) {
        return {
            current_page: 1,
            from: 0,
            last_page: 1,
            per_page: rows.value.length || 1,
            to: 0,
            total: 0,
        };
    }
    return meta;
});

const paginationLabel = computed(() => {
    const total = paginationMeta.value.total ?? 0;
    if (!total) {
        return 'Showing 0 rows';
    }

    const noun = total === 1 ? 'row' : 'rows';
    const from = paginationMeta.value.from ?? 0;
    const to = paginationMeta.value.to ?? 0;
    const lastPage = paginationMeta.value.last_page ?? 1;

    if (lastPage <= 1) {
        return `Showing ${total} ${noun}`;
    }

    return `Showing ${from} to ${to} of ${total} ${noun}`;
});

const totalColumns = computed(() => {
    const hasActions = props.showActions && ((props.rowActions && props.rowActions.length) || props.actionsLabel);
    return props.columns.length + (hasActions ? 1 : 0);
});

const perPageOptions = computed<number[]>(() => {
    const options = props.perPageOptions && props.perPageOptions.length ? props.perPageOptions : [5, 10, 25, 50, 100];
    return Array.from(new Set(options.map((option) => Number(option)).filter((option) => option > 0))).sort((a, b) => a - b);
});

const currentPerPage = computed(() => paginationMeta.value.per_page ?? perPageOptions.value[0] ?? 10);

const handlePerPageChange = (value: AcceptableValue) => {
    if (value === null || typeof value === 'boolean') {
        return;
    }
    const parsed = Number(value);
    if (!Number.isNaN(parsed) && parsed > 0 && parsed !== currentPerPage.value) {
        emit('update:perPage', parsed);
    }
};

const isLoading = computed(() => props.loading);
const hasRows = computed(() => rows.value.length > 0);
const showSkeleton = computed(() => isLoading.value && !hasRows.value);
const showEmpty = computed(() => !isLoading.value && !hasRows.value);

defineSlots<{
    actions(props: { item: Record<string, unknown> }): unknown;
    empty?(): unknown;
}>();
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
            <div class="text-center md:text-left">
                <Input type="search" placeholder="Search..." />
            </div>
            <div class="flex items-center justify-center gap-2 md:justify-end">
                <Select :model-value="String(currentPerPage)" @update:modelValue="handlePerPageChange">
                    <SelectTrigger class="w-16">
                        <SelectValue :placeholder="perPageLabel" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem v-for="option in perPageOptions" :key="option" :value="String(option)">
                                {{ option }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
                <span class="text-sm text-muted-foreground">rows per page</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead v-for="col in props.columns" :key="String(col.field)" :class="col.class">
                            {{ col.label }}
                        </TableHead>
                        <TableHead
                            v-if="props.showActions && (props.actionsLabel || (props.rowActions && props.rowActions.length))"
                            class="text-right">
                            {{ props.actionsLabel ?? 'Action' }}
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="showSkeleton">
                        <TableRow v-for="n in 3" :key="`skeleton-${n}`">
                            <TableCell :colspan="totalColumns">
                                <Skeleton class="h-4 w-full" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else-if="hasRows">
                        <TableRow
                            v-for="item in rows"
                            :key="(item as any)[props.rowKey]"
                            class="transition-opacity"
                            :class="isLoading ? 'pointer-events-none opacity-60' : ''">
                            <TableCell v-for="col in props.columns" :key="String(col.field)" :class="[props.cellClass, col.class]">
                                {{ col.formatter ? col.formatter(getCellValue(item, col.field as any), item) : getCellValue(item, col.field as any) }}
                            </TableCell>
                            <TableCell v-if="props.showActions && props.rowActions && props.rowActions.length" class="text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <Button
                                        v-for="(action, idx) in props.rowActions"
                                        :key="`${String((item as any)[props.rowKey])}-${idx}`"
                                        variant="outline"
                                        size="sm"
                                        class="cursor-pointer"
                                        v-can="action.can as any"
                                        @click="handleActionClick(action, item)">
                                        <component v-if="action.icon" :is="action.icon" class="mr-1 h-4 w-4" />
                                        {{ action.label }}
                                    </Button>
                                </div>
                            </TableCell>
                            <TableCell v-else-if="props.showActions && props.actionsLabel">
                                <slot name="actions" :item="item" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else-if="showEmpty">
                        <TableEmpty :colspan="totalColumns">
                            <slot name="empty">
                                <span class="text-sm text-muted-foreground">{{ props.emptyState }}</span>
                            </slot>
                        </TableEmpty>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
            <div class="text-center text-sm text-muted-foreground md:text-left">
                {{ paginationLabel }}
            </div>
            <div class="text-center md:text-right">
                <Pagination
                    v-slot="{ page }"
                    :items-per-page="paginationMeta.per_page"
                    :total="paginationMeta.total"
                    :default-page="paginationMeta.current_page"
                    @update:page="(p) => emit('update:page', p)">
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />
                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === page">
                                {{ item.value }}
                            </PaginationItem>
                            <PaginationEllipsis v-else-if="item.type === 'ellipsis'" :index="index" />
                        </template>
                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>

        <AlertDialog v-model:open="isConfirmOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ pendingAction?.action.confirm?.title ?? 'Are you sure?' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ pendingAction?.action.confirm?.description ?? 'This action cannot be undone.' }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="cancelAction" class="cursor-pointer">Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="confirmAction" class="cursor-pointer">Confirm</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
