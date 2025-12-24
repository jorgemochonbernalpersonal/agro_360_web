import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        // Optimizaciones de producción
        cssCodeSplit: true,
        sourcemap: false, // Desactivar sourcemaps en producción para reducir tamaño
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Eliminar console.log en producción
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug'],
            },
        },
        rollupOptions: {
            output: {
                // Separar vendor code para mejor caché
                manualChunks: (id) => {
                    if (id.includes('node_modules')) {
                        if (id.includes('axios')) {
                            return 'vendor-axios';
                        }
                        if (id.includes('livewire') || id.includes('alpine')) {
                            return 'vendor-livewire';
                        }
                        return 'vendor';
                    }
                },
                // Optimizar nombres de chunks
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.css')) {
                        return 'css/[name]-[hash][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
        // Límite de tamaño de chunk
        chunkSizeWarningLimit: 1000,
        // Optimizar assets
        assetsInlineLimit: 4096, // Inline assets menores a 4KB
    },
});
