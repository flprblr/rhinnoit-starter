<script setup lang="ts">
defineOptions({ name: 'SimpleTable' });
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
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = withDefaults(
    defineProps<{
        columns: TableColumn[];
        items: any[];
        itemsPerPage?: number;
        total?: number;
        currentPage?: number;
        rowKey?: string;
        actionsLabel?: string;
        showActions?: boolean;
        rowActions?: RowAction[];
        indexRoute?: string; // e.g. 'maintainers.users.index'
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
    (e: 'row:action', payload: { key: string; id: string | number; item: any }): void;
}>();

const isConfirmOpen = ref(false);
const pendingAction = ref<{ action: RowAction; item: any } | null>(null);

const getCellValue = (item: any, field: string | number | symbol) => {
    return (item as any)[field as any];
};

const handleActionClick = (action: RowAction, item: any) => {
    if (action.confirm) {
        pendingAction.value = { action, item };
        isConfirmOpen.value = true;
        return;
    }
    if (action.type === 'route' && action.route) {
        const param = action.paramFrom ? (item as any)[action.paramFrom as any] : (item as any)[props.rowKey as any];
        const method = action.method ?? 'get';
        router.visit(route(action.route as any, param), { method });
        return;
    }
    emit('row:action', { key: action.key, id: (item as any)[props.rowKey as any], item });
};

const confirmAction = () => {
    if (pendingAction.value) {
        const { action, item } = pendingAction.value;
        const id = (item as any)[props.rowKey as any];
        if (action.type === 'route' && action.route) {
            const param = action.paramFrom ? (item as any)[action.paramFrom as any] : id;
            const method = action.method ?? 'get';
            router.visit(route(action.route as any, param), { method });
        } else {
            emit('row:action', { key: action.key, id, item });
        }
    }
    isConfirmOpen.value = false;
    pendingAction.value = null;
};

const cancelAction = () => {
    isConfirmOpen.value = false;
    pendingAction.value = null;
};

defineSlots<{
    actions(props: { item: any }): any;
}>();
</script>

<template>
    <div class="simple-table space-y-3">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead v-for="col in props.columns" :key="String(col.field)" :class="col.class">{{ col.label }}</TableHead>
                    <TableHead v-if="props.showActions && (props.actionsLabel || (props.rowActions && props.rowActions.length))" class="text-right">
                        {{ props.actionsLabel ?? 'Action' }}
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="item in props.items" :key="(item as any)[props.rowKey]">
                    <TableCell v-for="col in props.columns" :key="String(col.field)">
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
                                @click="handleActionClick(action, item)"
                            >
                                <component v-if="action.icon" :is="action.icon" class="mr-1 h-4 w-4" />
                                {{ action.label }}
                            </Button>
                        </div>
                    </TableCell>
                    <TableCell v-else-if="props.showActions && props.actionsLabel" class="text-right">
                        <slot name="actions" :item="item" />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <Pagination
            v-slot="{ page }"
            :items-per-page="props.itemsPerPage"
            :total="props.total"
            :default-page="props.currentPage"
            @update:page="
                (p) => {
                    if (props.indexRoute) {
                        router.get(route(props.indexRoute as any), { page: p }, { preserveScroll: true, preserveState: true, replace: true });
                    } else {
                        emit('update:page', p);
                    }
                }
            "
        >
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

        <AlertDialog v-model:open="isConfirmOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ pendingAction?.action.confirm?.title ?? '¿Estás seguro?' }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ pendingAction?.action.confirm?.description ?? 'Esta acción no se puede deshacer.' }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="cancelAction" class="cursor-pointer">Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmAction" class="cursor-pointer">Confirmar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
