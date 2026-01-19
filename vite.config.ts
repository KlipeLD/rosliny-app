// import { wayfinder } from '@laravel/vite-plugin-wayfinder';
// import tailwindcss from '@tailwindcss/vite';
// import vue from '@vitejs/plugin-vue';
// import laravel from 'laravel-vite-plugin';
// import { defineConfig } from 'vite';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/js/app.ts','resources/scss/app.scss'],
//             ssr: 'resources/js/ssr.ts',
//             refresh: true,
//         }),
//         tailwindcss(),
//         wayfinder({
//             formVariants: true,
//         }),
//         vue({
//             template: {
//                 transformAssetUrls: {
//                     base: null,
//                     includeAbsolute: false,
//                 },
//             },
//         }),
//     ],
// });

import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/scss/app.scss'],
      refresh: true,
    }),
  ],
});