import React from "react";
import { InertiaLink } from "@inertiajs/inertia-react";

const A = (props) => {
    const target = props.target ? props.target : "_blank";

    return <a {...props} target={target} />;
};

const Link = (props) => {
    return <InertiaLink {...props} />;
};

const BaseLink = ({ external, ...props }) => {
    if (external) {
        return <A {...props} />;
    }

    let { as, ...otherProps } = props;

    if (as === "a" && props.method !== "get") {
        as = "button";
    }

    return <Link as={as} {...otherProps} />;
};

export default BaseLink;
