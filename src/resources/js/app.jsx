import "./bootstrap";
import "../css/app.css";
import React from "react";
import { render } from "react-dom";
import { createInertiaApp } from "@inertiajs/inertia-react";
import { InertiaProgress } from "@inertiajs/progress";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { Provider } from "react-redux";
import { store } from "@/Reducers/store";
import { ErrorBoundary } from "@/Components/ErrorBoundary";

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText ||
    "SCDS Membership";

const Wrapper = (props) => {
    return <Provider store={store} {...props} />;
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        Promise.any([
            resolvePageComponent(
                `./Pages/${name}.jsx`,
                import.meta.glob("./Pages/**/*.jsx")
            ),
            resolvePageComponent(
                `./Pages/${name}.tsx`,
                import.meta.glob("./Pages/**/*.tsx")
            ),
        ]),
    setup({ el, App, props }) {
        return render(
            <ErrorBoundary>
                <Wrapper>
                    <App {...props} />
                </Wrapper>
            </ErrorBoundary>,
            el
        );
    },
});

InertiaProgress.init({ color: "#4F46E5" });
