import React from "react";

type Props = {
    children: string | React.ReactNode;
    colour?:
        | "red"
        | "yellow"
        | "green"
        | "blue"
        | "indigo"
        | "purple"
        | "pink"
        | "gray";
};

export const Badge: React.FC<Props> = ({ children, colour }) => {
    const classNames = `bg-${colour || "gray"}-100 text-${
        colour || "gray"
    }-800`;

    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${classNames}`}
        >
            {children}
        </span>
    );
};

export default Badge;
