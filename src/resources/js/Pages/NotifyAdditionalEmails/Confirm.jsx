import React from "react";
import AuthServices from "@/Layouts/AuthServices";
import { Head } from "@inertiajs/inertia-react";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import { Inertia } from "@inertiajs/inertia";
import Alert from "@/Components/Alert";
import Hidden from "@/Components/Form/Hidden";

const Confirm = (props) => {
    const onSubmit = (values, formikBag) => {
        Inertia.post(route("notify_additional_emails.confirm"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    let response = null;

    if (props?.flash?.success) {
        response = (
            <div className="text-sm text-gray-600">
                You can now close this tab.
            </div>
        );
    } else if (props.already) {
        response = (
            <>
                <Alert variant="warning" title="Warning">
                    Your email address has already been verified.
                </Alert>

                <div className="text-sm text-gray-600">
                    You can now close this tab.
                </div>
            </>
        );
    } else {
        response = (
            <>
                <div className="mb-4 text-sm text-gray-600">
                    Please confirm you wish to receive copies of squad update
                    emails sent to {props.user} by the {props.tenant.name} team.
                </div>

                <Form
                    initialValues={{
                        data: "",
                    }}
                    onSubmit={onSubmit}
                    validationSchema={yup.object().shape({
                        data: yup.string().required("Signed data is required"),
                    })}
                    onSubmit={onSubmit}
                    submitTitle="Confirm"
                    submitClass="w-full"
                    hideClear
                    alwaysDirty
                >
                    <Hidden name="data" />
                </Form>
            </>
        );
    }

    return (
        <>
            <Head title="Confirm additional email" />

            {response}
        </>
    );
};

Confirm.layout = (page) => (
    <AuthServices title="Confirm additional email" children={page} />
);

export default Confirm;
