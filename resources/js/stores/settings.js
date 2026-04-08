import { defineStore } from 'pinia';
import axios from '../axios';

export const useSettingsStore = defineStore('settings', {
    state: () => ({
        site_title: 'CMS',
        site_tagline: '',
        meta_description: '',
        primary_color: '#4f46e5',
        footer_about: '',
        footer_contact_email: '',
        footer_contact_phone: '',
        footer_contact_address: '',
        loaded: false,
    }),

    actions: {
        /**
         * Apply a settings object to the store and propagate visible side
         * effects (document.title, <meta description>, brand CSS variable).
         */
        apply(payload = {}) {
            if (payload.site_title) this.site_title = payload.site_title;
            if (payload.site_tagline !== undefined) this.site_tagline = payload.site_tagline;
            if (payload.meta_description !== undefined) this.meta_description = payload.meta_description;
            if (payload.primary_color) this.primary_color = payload.primary_color;
            if (payload.footer_about !== undefined) this.footer_about = payload.footer_about;
            if (payload.footer_contact_email !== undefined) this.footer_contact_email = payload.footer_contact_email;
            if (payload.footer_contact_phone !== undefined) this.footer_contact_phone = payload.footer_contact_phone;
            if (payload.footer_contact_address !== undefined) this.footer_contact_address = payload.footer_contact_address;
            this.loaded = true;
            this.applyToDocument();
        },

        applyToDocument() {
            if (typeof document === 'undefined') return;

            if (this.site_title) document.title = this.site_title;

            // <meta name="description">
            let meta = document.querySelector('meta[name="description"]');
            if (!meta) {
                meta = document.createElement('meta');
                meta.setAttribute('name', 'description');
                document.head.appendChild(meta);
            }
            meta.setAttribute('content', this.meta_description || '');

            // CSS variable for brand color — usable as var(--brand-color)
            if (this.primary_color) {
                document.documentElement.style.setProperty('--brand-color', this.primary_color);
            }
        },

        /**
         * Fetch the public-safe settings (no auth required).
         */
        async fetchPublic() {
            try {
                const res = await axios.get('/public/settings');
                this.apply(res.data.data);
            } catch (err) {
                console.warn('Failed to load public settings', err);
            }
        },
    },
});
