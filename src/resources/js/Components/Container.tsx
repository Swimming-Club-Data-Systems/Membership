import React from "react";

type ContainerProps = {
    noMargin?: boolean;
    className?: string;
    fluid?: boolean;
    children: React.ReactNode;
};

const Container = (props: ContainerProps) => {
    let classes = "";

    if (!props.noMargin) {
        classes = " px-4 ";
    }

    if (props.className) {
        classes += props.className;
    }

    if (!props.fluid) {
        classes += "max-w-7xl lg:px-8";
    }

    return (
        // <div className="bg-gray-100 dark:bg-slate-900 dark:text-gray-100">
        <div className={`mx-auto sm:px-4 ${classes}`}>{props.children}</div>
        // </div>
    );
};

export default Container;
