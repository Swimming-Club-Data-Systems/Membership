import React from "react";
import BaseLink, { LinkProps as BaseLinkProps } from "@/Components/BaseLink";

const Link = ({ className = "", children, ...props }: BaseLinkProps) => {
    return (
        <BaseLink
            className={`text-indigo-600 hover:text-indigo-700 hover:underline ${
                className || ""
            }`}
            {...props}
        >
            {children}
        </BaseLink>
    );
};

export default Link;
