import React from "react";
import { InertiaLink, InertiaLinkProps } from "@inertiajs/inertia-react";

interface AProps extends React.AriaAttributes {
    href: string;
    target?: "_self" | "_blank";
}

const A: React.FC<AProps> = (props) => {
    const target = props.target ? props.target : "_blank";

    return <a {...props} target={target} />;
};

const Link: React.FC<InertiaLinkProps> = (props) => {
    return <InertiaLink {...props} />;
};

interface BaseLinkProps extends InertiaLinkProps {
    href: string;
    external?: boolean;
    target?: "_self" | "_blank";
}

const BaseLink: React.FC<BaseLinkProps> = ({ external, ...props }) => {
    if (external) {
        return <A {...props} />;
    }

    // Ignore prefer const as we're destructuring then maybe changing "as"
    // eslint-disable-next-line prefer-const
    let { as, ...otherProps } = props;

    if (as === "a" && props.method !== "get") {
        as = "button";
    }

    return <Link as={as} {...otherProps} />;
};

export default BaseLink;
