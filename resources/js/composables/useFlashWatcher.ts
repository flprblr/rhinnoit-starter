import type { Flash } from '@/types/inertia';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

export function useFlashWatcher() {
    const page = usePage();

    watch(
        () => page.props.flash,
        (flash) => {
            const casted = flash as Flash | undefined;

            if (casted?.success) {
                toast.success(casted.success);
            }
            if (casted?.error) {
                toast.error(casted.error);
            }
        },
        { immediate: true, deep: true },
    );
}
