import React from "react";
import Button from "@/Components/Button";
import { Inertia } from "@inertiajs/inertia";

const ButtonLink = ({ href, external, ...props }) => {
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
