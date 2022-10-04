import React from "react";

export const Dd = (props) => {
    return (
        <dt className="text-sm font-medium text-gray-500">{props.children}</dt>
    );
};

export const Dt = (props) => {
    let classes = "mt-1 text-sm text-gray-900 ";
    if (props.className) {
        classes += props.className;
    }
    return <dd className={classes}>{props.children}</dd>;
};
