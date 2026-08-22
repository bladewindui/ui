import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import { resolve } from 'path'

const isWatch = process.argv.includes('--watch')

export default defineConfig({
    plugins: [
        tailwindcss(),
    ],

    build: {
        // Output the compiled CSS bundle into the core package so it is
        // published to users via BladewindCoreServiceProvider's bladewind-public tag.
        outDir: 'packages/core/public',

        // Only enable watch config when --watch is explicitly passed (npm run watch).
        // Without this guard, any non-null build.watch object activates Rollup's watcher
        // even for plain `npm run build`, keeping the process alive after the build.
        ...(isWatch ? {
            watch: {
                exclude: ['packages/core/public/css/**'],
                chokidar: {
                    ignored: ['**/packages/core/public/css/**'],
                },
            },
        } : {}),

        emptyOutDir: false,

        rollupOptions: {
            input: {
                'css/bladewind-ui.min': resolve(__dirname, 'tailwind.css'),
                // same bundle without Preflight, for apps that compile their own
                // Tailwind and do not want the document reset twice. See #589.
                'css/bladewind-ui-no-preflight.min': resolve(__dirname, 'tailwind-no-preflight.css'),
            },
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) {
                        const base = assetInfo.name.includes('no-preflight')
                            ? 'bladewind-ui-no-preflight.min'
                            : 'bladewind-ui.min'

                        return `css/${base}[extname]`
                    }
                    return 'assets/[name]-[hash][extname]'
                },
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
            },
        },
    },
})
