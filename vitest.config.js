import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        globals: true,
        environment: 'node',
        include: ['resources/js/**/*.spec.js', 'resources/js/**/*.test.js'],
        // No setup file needed yet — we only test pure utility modules.
        // When we add component tests, switch `environment` to 'jsdom' and
        // install @vue/test-utils + jsdom.
    },
});
