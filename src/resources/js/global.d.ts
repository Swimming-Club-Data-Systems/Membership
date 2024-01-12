import routeFn from "ziggy-js";
import Echo from "laravel-echo";

declare global {
    var route: typeof routeFn;
    interface Window {
        Echo: Echo;
    }
}

// Run `php artisan ziggy:generate --types` to generate types
