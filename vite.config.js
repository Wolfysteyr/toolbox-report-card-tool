import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import {
    defineConfig
} from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: 'react-front/src/main.jsx', 
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: {
            '@': '/react-front/src',
        },
    },
});