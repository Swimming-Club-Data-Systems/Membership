import React from "react";
import { createRoot } from "react-dom/server";
import createServer from "@inertiajs/react/server";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ErrorBoundary } from "@/Components/ErrorBoundary";
import { Provider } from "react-redux";
import { store } from "@/Reducers/store";

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText ||
    "SCDS Membership";

// eslint-disable-next-line react/prop-types
const Wrapper = ({ children, ...props }) => {
    return (
        <Provider store={store} {...props}>
            {children}
        </Provider>
    );
};

createServer((page) =>
    createInertiaApp({
        progress: {
            color: "#4F46E5",
        },
        title: (title) => `${title} - ${appName}`,
        resolve: (name) =>
            Promise.any([
                resolvePageComponent(
                    `./Pages/${name}.jsx`,
                    import.meta.glob("./Pages/**/*.jsx"),
                ),
                resolvePageComponent(
                    `./Pages/${name}.tsx`,
                    import.meta.glob("./Pages/**/*.tsx"),
                ),
            ]),
        setup({ el, App, props }) {
            const root = createRoot(el);
            return root.render(
                <ErrorBoundary>
                    <Wrapper>
                        <App {...props} />
                    </Wrapper>
                </ErrorBoundary>,
            );
        },
    }),
);
