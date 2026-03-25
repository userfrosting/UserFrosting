/// <reference types="vitest" />
import { defineConfig, configDefaults } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import ViteYaml from '@modyfi/vite-plugin-yaml'

export default defineConfig({
    plugins: [vue(), ViteYaml()],
    test: {
        coverage: {
            enabled: true,
            reportsDirectory: './_meta/_coverage',
            include: ['app/assets/**/*.*'],
            exclude: ['app/assets/main.ts'],
        },
        reporters: ['default', 'junit'],
        outputFile: './_meta/junit_frontend.xml',
        environment: 'happy-dom',
        exclude: [
            ...configDefaults.exclude,
            './vendor/**/*.*',
        ],
    },
})
