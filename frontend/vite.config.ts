import { fileURLToPath, URL } from "node:url";

import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import vueDevTools from "vite-plugin-vue-devtools";

// https://vite.dev/config/
export default defineConfig({
    plugins: [vue(), vueDevTools()],
    // Proxy configuration to redirect API calls to the backend server
    // This is used for development to avoid CORS issues by proxying requests to a local server.
    server: {
        proxy: {
            "/api": {
                target: "http://localhost:8000", // Proxy requests to the API server running on localhost:8000
                changeOrigin: true, // Change the origin of the request to the target server
                secure: false, // Disable SSL verification for development purposes
            },
        },
    },
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("./src", import.meta.url)),
        },
    },
});
