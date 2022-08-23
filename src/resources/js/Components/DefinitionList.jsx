import React from "react";

export const Dl = (props) => {
    return (
        <dl className="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
            {props.children}
        </dl>
    );
};
