import React from "react";
import CentralAuthServices from "@/Layouts/CentralAuthServices";
import { Head, router } from "@inertiajs/react";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import Alert from "@/Components/Alert";
import * as yup from "yup";

export default function ForgotPassword({ status }) {
    const onSubmit = (values, formikBag) => {
        router.post(route("central.password.email"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    return (
        <CentralAuthServices title="Forgot password">
            <Head title="Forgot Password" />

            <div className="mb-4 text-sm text-gray-500 leading-normal">
                Forgot your password? No problem. Just let us know your email
                address and we will email you a password reset link that will
                allow you to choose a new one.
            </div>

            {status && (
                <Alert title="Success" className="mb-4">
                    {status}
                </Alert>
            )}

            <Form
                initialValues={{
                    email: "",
                }}
                validationSchema={yup.object().shape({
                    email: yup
                        .string()
                        .required("An email address is required")
                        .email("Your email address must be valid"),
                })}
                onSubmit={onSubmit}
                submitTitle="Email Password Reset Link"
                hideClear
            >
                <TextInput name="email" type="email" autoFocus />
            </Form>
        </CentralAuthServices>
    );
}
