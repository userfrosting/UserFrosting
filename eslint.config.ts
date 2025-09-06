import js from '@eslint/js'
import tseslint from 'typescript-eslint'
import pluginVue from 'eslint-plugin-vue'
import { defineConfig } from 'eslint/config'

export default defineConfig([
    {
        files: ['**/*.{js,mjs,cjs,ts,mts,cts,vue}'],
        plugins: { js },
        extends: ['js/recommended']
    },
    tseslint.configs.recommended,
    pluginVue.configs['flat/essential'],
    { files: ['**/*.vue'], languageOptions: { parserOptions: { parser: tseslint.parser } } },
    {
        languageOptions: {
            parserOptions: {
                tsconfigRootDir: __dirname
            }
        }
    },
    {
        rules: {
            '@typescript-eslint/no-explicit-any': 'off'
        }
    }
])
