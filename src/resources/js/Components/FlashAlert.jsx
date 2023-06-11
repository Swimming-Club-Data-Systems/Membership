import React, { useContext } from "react";
import Alert from "./Alert";
import { usePage } from "@inertiajs/react";
import { FormSpecialContext } from "@/Components/Form/Form";

const FlashAlert = ({ className, bag = null }) => {
    const formContext = useContext(FormSpecialContext);

    const bagName = bag || formContext?.formName;

    const flash =
        (bagName ? usePage().props?.flash[bagName] : usePage().props?.flash) ??
        {};

    return (
        <>
            {flash.error && (
                <Alert
                    variant="error"
                    className={className}
                    title="Error"
                    // dismissable
                >
                    {flash.error}
                </Alert>
            )}
            {flash.warning && (
                <Alert
                    variant="warning"
                    className={className}
                    title="Warning"
                    // dismissable
                >
                    {flash.warning}
                </Alert>
            )}
            {flash.success && (
                <Alert
                    variant="success"
                    className={className}
                    title="Success"
                    // dismissable
                >
                    {flash.success}
                </Alert>
            )}
        </>
    );
};

export default FlashAlert;
