import React from "react";
import BaseLink, { LinkProps as BaseLinkProps } from "@/Components/BaseLink";

const Link = ({ className = "", children, ...props }: BaseLinkProps) => {
    return (
        <BaseLink
            className={`text-indigo-600 hover:text-indigo-700 underline focus:ring-4 focus:ring-offset-4 focus:ring-amber-500 ${
                className || ""
            }`}
            {...props}
        >
            {children}
        </BaseLink>
    );
};

export default Link;
