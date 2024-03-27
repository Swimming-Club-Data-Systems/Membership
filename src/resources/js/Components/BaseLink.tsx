import React from "react";
import { InertiaLinkProps, Link as InertiaLink } from "@inertiajs/react";

interface AProps {
    href: string;
    target?: string;
    external?: boolean;
    as?: string;
    children: React.ReactNode;
    className?: string;
    download?: boolean;
}

const A: React.FC<AProps> = ({ children, ...props }) => {
    const target = props.target ? props.target : "_blank";

    return (
        <a {...props} target={target}>
            {children}
        </a>
    );
};

export interface LinkProps extends AProps {
    method?: InertiaLinkProps["method"];
}

const Link: React.FC<LinkProps> = (props) => {
    return <InertiaLink {...props} />;
};

const BaseLink: React.FC<LinkProps> = ({ external, children, ...props }) => {
    if (external || props.target === "_blank") {
        return <A {...props}>{children}</A>;
    }

    // eslint-disable-next-line prefer-const
    let { as, ...otherProps } = props;

    if (as === "a" && props.method !== "get") {
        as = "button";
    }

    return (
        <Link as={as} {...otherProps}>
            {children}
        </Link>
    );
};

export default BaseLink;
