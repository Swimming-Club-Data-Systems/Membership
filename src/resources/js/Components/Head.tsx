import React, { useEffect } from "react";
import { Head as InertiaHead } from "@inertiajs/react";
import { setKeys, store } from "@/Reducers/store";

type HeadProps = {
    title: string;
    subtitle?: string;
    breadcrumbs?: {
        name: string;
        route: string;
        routeParams?:
            | string
            | number
            | Record<string, unknown>
            | (string | number)[];
    }[];
};

export const Head: React.FC<HeadProps> = (props) => {
    useEffect(() => {
        store.dispatch(
            setKeys([
                ["title", props.title],
                ["subtitle", props.subtitle],
                ["breadcrumbs", props.breadcrumbs],
            ]),
        );
        return () => {
            store.dispatch(
                setKeys([
                    ["title", null],
                    ["subtitle", null],
                    ["breadcrumbs", null],
                ]),
            );
        };
    }, [props.title, props.subtitle, props.breadcrumbs]);

    return (
        <>
            <InertiaHead title={props.title} />
        </>
    );
};

export default Head;
