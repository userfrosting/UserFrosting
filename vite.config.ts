import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import ViteYaml from '@modyfi/vite-plugin-yaml'
import { existsSync } from 'fs'
import { resolve } from 'path'

// Get vite port from env, default to 3000
const vitePort = parseInt(process.env.VITE_PORT || '5173', 10)

// Detect if we're in a monorepo by checking for workspace root
const isMonorepo = existsSync(resolve(__dirname, '../../package.json')) && 
                   existsSync(resolve(__dirname, '../../packages'))

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        ViteYaml(),
        vueDevTools({
            appendTo: 'app/assets/main.ts'
        })
    ],
    // In monorepo, use 'development' condition to resolve to source TS files for HMR
    // In standalone usage, use 'import' to resolve to published dist files
    resolve: isMonorepo ? {
        conditions: ['development', 'import']
    } : undefined,
    server: {
        host: true, // Allows external access (needed for Docker)
        strictPort: true,
        port: vitePort,
        origin: `http://localhost:${vitePort}`,
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
    // Force optimization of UiKit (not module packages) in dev mode 
    // to avoid the error:
    // "importing binding name 'default' cannot be resolved by star export entries"
    // Sprinkle packages are pre-built but during monorepo development we
    // still treat them as source code to enable hot module reload
    optimizeDeps: {
        include: ['uikit', 'uikit/dist/js/uikit-icons'],
        exclude: [
            '@userfrosting/sprinkle-core',
            '@userfrosting/sprinkle-account',
            '@userfrosting/sprinkle-admin',
            '@userfrosting/theme-pink-cupcake'
        ]
    }
})
