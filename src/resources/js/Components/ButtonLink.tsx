import React from "react";
import Button from "@/Components/Button.jsx";
import { Inertia } from "@inertiajs/inertia";
import { Props } from "./Button.jsx";

interface ButtonLinkProps extends Props {
    href: string;
    external?: boolean;
}

const ButtonLink: React.FC<ButtonLinkProps> = ({
    href,
    external,
    ...props
}) => {
    const onClick = () => {
        if (external) {
            window.location.href = href;
        } else {
            Inertia.visit(href);
        }
    };

    return <Button onClick={onClick} {...props} />;
};

export default ButtonLink;
