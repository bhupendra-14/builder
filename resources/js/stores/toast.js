import { defineStore } from 'pinia';

let nextId = 1;

/**
 * Global toast notification store. Use the helper methods (success, error,
 * info, warning) anywhere in the app to push a non-blocking notification.
 *
 *   import { useToast } from '@/stores/toast';
 *   const toast = useToast();
 *   toast.success('Saved!');
 *   toast.error('Something went wrong', { title: 'Save failed' });
 */
export const useToast = defineStore('toast', {
    state: () => ({
        items: [],
    }),

    actions: {
        /**
         * Push a toast.
         * @param {object} opts
         * @param {'success'|'error'|'info'|'warning'} opts.type
         * @param {string} opts.message
         * @param {string} [opts.title]
         * @param {number} [opts.duration] ms; 0 = sticky
         */
        push({ type = 'info', message, title = '', duration = 4000 }) {
            const id = nextId++;
            this.items.push({ id, type, message, title, duration });
            if (duration > 0) {
                setTimeout(() => this.dismiss(id), duration);
            }
            return id;
        },

        success(message, opts = {}) {
            return this.push({ type: 'success', message, ...opts });
        },
        error(message, opts = {}) {
            return this.push({ type: 'error', message, duration: 6000, ...opts });
        },
        info(message, opts = {}) {
            return this.push({ type: 'info', message, ...opts });
        },
        warning(message, opts = {}) {
            return this.push({ type: 'warning', message, ...opts });
        },

        dismiss(id) {
            this.items = this.items.filter(t => t.id !== id);
        },

        clear() {
            this.items = [];
        },
    },
});
