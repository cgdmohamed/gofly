// Builds one IIFE bundle + one stylesheet into ../dist, loaded by
// includes/class-admin.php with wp_enqueue_script/style (no module loader needed).
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
	plugins: [vue()],
	define: { 'process.env.NODE_ENV': '"production"', __VUE_OPTIONS_API__: false, __VUE_PROD_DEVTOOLS__: false, __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false },
	build: {
		outDir: '../dist',
		emptyOutDir: true,
		cssCodeSplit: false,
		lib: { entry: 'src/main.js', name: 'GoflyProgramBuilder', formats: ['iife'], fileName: () => 'app.js', cssFileName: 'app' },
		rollupOptions: { output: { assetFileNames: 'app.[ext]', inlineDynamicImports: true } },
	},
});
