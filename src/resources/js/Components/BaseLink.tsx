import React from "react";
import { Link as InertiaLink } from "@inertiajs/react";

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
    method?: string;
}

const Link: React.FC<LinkProps> = (props) => {
    return <InertiaLink {...props} />;
};

const BaseLink: React.FC<LinkProps> = ({ external, ...props }) => {
    if (external) {
        return <A {...props} />;
    }

    // eslint-disable-next-line prefer-const
    let { as, ...otherProps } = props;

    if (as === "a" && props.method !== "get") {
        as = "button";
    }

    return <Link as={as} {...otherProps} />;
};

export default BaseLink;
