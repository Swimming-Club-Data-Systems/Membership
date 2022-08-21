import React from "react";
import Alert from "./Alert";
import { usePage } from "@inertiajs/inertia-react";

const FlashAlert = ({ className }) => {
    const { flash } = usePage().props;

    return (
        <>
            {flash.error && (
                <Alert variant="error" className={className} title="Error" dismissable>
                    {flash.error}
                </Alert>
            )}
            {flash.warning && (
                <Alert variant="warning" className title="Warning" dismissable>
                    {flash.warning}
                </Alert>
            )}
            {flash.success && (
                <Alert variant="success" className title="Success" dismissable>
                    {flash.success}
                </Alert>
            )}
        </>
    );
};

export default FlashAlert;
