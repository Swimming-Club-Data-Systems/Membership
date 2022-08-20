import React from "react";
import Alert from "./Alert";
import { usePage } from "@inertiajs/inertia-react";

const FlashAlert = ({ className }) => {
    const { flash } = usePage().props;

    return (
        <>
            {flash.error && (
                <Alert variant="error" className={className} title="Error">
                    {flash.error}
                </Alert>
            )}
            {flash.warning && (
                <Alert variant="warning" className title="Warning">
                    {flash.warning}
                </Alert>
            )}
            {flash.success && (
                <Alert variant="success" className title="Success">
                    {flash.success}
                </Alert>
            )}
        </>
    );
};

export default FlashAlert;
