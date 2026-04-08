import { defineStore } from 'pinia';

/**
 * Promise-based confirm dialog store. Use it as a drop-in replacement for
 * window.confirm():
 *
 *   import { useConfirm } from '@/stores/confirm';
 *   const confirm = useConfirm();
 *   const ok = await confirm.ask({
 *     title: 'Delete this user?',
 *     message: 'This action cannot be undone.',
 *     confirmLabel: 'Delete',
 *     variant: 'danger',
 *   });
 *   if (ok) { ... }
 */
export const useConfirm = defineStore('confirm', {
    state: () => ({
        open: false,
        title: '',
        message: '',
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        variant: 'primary', // 'primary' | 'danger'
        _resolve: null,
    }),

    actions: {
        ask(opts = {}) {
            this.title = opts.title || 'Are you sure?';
            this.message = opts.message || '';
            this.confirmLabel = opts.confirmLabel || 'Confirm';
            this.cancelLabel = opts.cancelLabel || 'Cancel';
            this.variant = opts.variant || 'primary';
            this.open = true;
            return new Promise((resolve) => {
                this._resolve = resolve;
            });
        },

        accept() {
            this.open = false;
            this._resolve?.(true);
            this._resolve = null;
        },

        cancel() {
            this.open = false;
            this._resolve?.(false);
            this._resolve = null;
        },
    },
});
