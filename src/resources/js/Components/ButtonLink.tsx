import React from "react";
import { router } from "@inertiajs/react";
import Button from "@/Components/Button.jsx";
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
            router.visit(href);
        }
    };

    return <Button onClick={onClick} {...props} />;
};

export default ButtonLink;
