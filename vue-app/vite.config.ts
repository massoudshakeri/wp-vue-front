/// <reference types="node" />
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath } from 'url'
import path, { dirname, resolve } from 'path'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// https://vite.dev/config/
export default defineConfig({
  base: '/',
  root: __dirname,
  plugins: [vue()],
  build: {
    // watch for changes:
    // https://vitejs.dev/config/build-options.html#build-watch
    sourcemap: true, // to get useful debugging
    minify: false, // disable minification
    outDir: path.resolve(__dirname, '../assets/dist'), // Output to ../assets/dist
    emptyOutDir: false,
    watch: {},
    rollupOptions: {
      input: {
        // main: path.resolve(__dirname, 'src/main.js'),
        loader: path.resolve(__dirname, './src/vue-loader.js'),
        // accountingassets: path.resolve(__dirname, 'src/accounting-assets-vue.js'),
      },
      // no hash in filenames, so it's always the same name
      output: {
        entryFileNames: '[name].bundle.js',      // output JS name
        chunkFileNames: 'chunk-[name].js',
        assetFileNames: 'style.css',      // output CSS name
      }
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, './src'),
    },
  },
  server: {
    // Ensure the dev server serves from the correct directory
    origin: 'http://localhost:5173',
    hmr: {
      protocol: 'ws',
      host: 'localhost'
    },
    cors: {
      origin: ['http://test.local', 'http://localhost:5173'],
      methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
      credentials: true
    }
  },
})
