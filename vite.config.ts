import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        vueDevTools({
            appendTo: 'app/assets/main.ts',
        })
    ],
    server: {
        strictPort: true,
        port: 3000,
        origin: 'http://localhost:3000'
    },
    root: 'app/assets/',
    base: '/assets/',
    build: {
        outDir: '../../public/assets',
        assetsDir: '',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                main: 'app/assets/main.ts'
            }
        }
    },
    // Fix uikit path issue
    // @see : https://github.com/uikit/uikit/issues/5024
    css: {
        preprocessorOptions: {
            less: {
                relativeUrls: "all",
            },
        },
    },
    // Force optimization of UiKit in dev mode to avoid to avoid the error:
    // "importing binding name 'default' cannot be resolved by star export entries"
    optimizeDeps: {
        include: ['uikit', 'uikit/dist/js/uikit-icons', 'limax'],
    }
})
