import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import ViteYaml from '@modyfi/vite-plugin-yaml'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        ViteYaml(),
        vueDevTools({
            appendTo: 'app/assets/main.ts'
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
                relativeUrls: 'all'
            }
        }
    },
    // Force optimization of UiKit and limax (not module packages) in dev mode 
    // to avoid the error:
    // "importing binding name 'default' cannot be resolved by star export entries"
    // Also, treat all sprinkles as source code (not prebuilt) and tell Vite 
    // not to prebundle them.
    optimizeDeps: {
        include: ['uikit', 'uikit/dist/js/uikit-icons', 'limax'],
        exclude: [
            '@userfrosting/sprinkle-core',
            '@userfrosting/sprinkle-account',
            '@userfrosting/sprinkle-admin',
            '@userfrosting/theme-pink-cupcake'
        ]
    }
})
