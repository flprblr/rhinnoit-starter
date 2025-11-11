<script setup lang="ts">
import { computed, ref } from 'vue';

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
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { RowAction, TableColumn } from '@/types';

defineOptions({ name: 'DataTable' });

const props = withDefaults(
    defineProps<{
        columns: TableColumn<Record<string, unknown>>[];
        items: Record<string, unknown>[];
        itemsPerPage?: number;
        total?: number;
        currentPage?: number;
        rowKey?: string;
        actionsLabel?: string;
        showActions?: boolean;
        rowActions?: RowAction[];
    }>(),
    {
        itemsPerPage: 10,
        total: 0,
        currentPage: 1,
        rowKey: 'id',
        showActions: true,
    },
);

const emit = defineEmits<{
    (e: 'update:page', value: number): void;
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

const paginationLabel = computed(() => {
    const total = props.total ?? 0;
    if (!total) {
        return 'Showing 0 entries';
    }
    const base = Math.max(props.currentPage - 1, 0) * props.itemsPerPage + 1;
    const from = Math.min(base, total);
    const count = props.items.length;
    const to = count ? Math.min(from + count - 1, total) : from;
    return `Showing ${from} to ${to} of ${total} entries`;
});

defineSlots<{
    actions(props: { item: Record<string, unknown> }): unknown;
}>();
</script>

<template>
    <div class="space-y-3">
        <div class="overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow class="h-10">
                        <TableHead v-for="col in props.columns" :key="String(col.field)" :class="[col.class, 'px-3 py-2 whitespace-nowrap']">
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
                    <TableRow v-for="item in props.items" :key="(item as any)[props.rowKey]" class="h-10">
                        <TableCell v-for="col in props.columns" :key="String(col.field)" :class="[col.class, 'px-3 py-2 whitespace-nowrap']">
                            {{ col.formatter ? col.formatter(getCellValue(item, col.field as any), item) : getCellValue(item, col.field as any) }}
                        </TableCell>
                        <TableCell
                            v-if="props.showActions && props.rowActions && props.rowActions.length"
                            class="px-3 py-2 text-right whitespace-nowrap">
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
                        <TableCell v-else-if="props.showActions && props.actionsLabel" class="px-3 py-2 text-right whitespace-nowrap">
                            <slot name="actions" :item="item" />
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
            <div class="text-center text-sm text-muted-foreground">
                {{ paginationLabel }}
            </div>
            <div class="text-center md:text-right">
                <Pagination
                    v-slot="{ page }"
                    :items-per-page="props.itemsPerPage"
                    :total="props.total"
                    :default-page="props.currentPage"
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
