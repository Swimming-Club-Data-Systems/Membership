import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/app.jsx", "resources/css/app.css"],
            ssr: "resources/js/ssr.jsx",
            refresh: true,
        }),
        react(),
    ],
    ssr: {
        noExternal: ["@inertiajs/server"],
    },
    css: {
        devSourcemap: true,
    },
});
