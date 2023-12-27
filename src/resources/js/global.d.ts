import routeFn from "ziggy-js";

declare global {
    var route: typeof routeFn;
}

// Run `php artisan ziggy:generate --types` to generate types
