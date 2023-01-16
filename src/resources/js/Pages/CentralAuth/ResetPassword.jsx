import React from "react";
import CentralAuthServices from "@/Layouts/CentralAuthServices";
import { Head, router } from "@inertiajs/react";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";

export default function ResetPassword({ token, email }) {
    const onSubmit = (values, formikBag) => {
        router.post(route("central.password.update"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    return (
        <CentralAuthServices title="Reset your account password">
            <Head title="Reset Password" />

            <Form
                initialValues={{
                    token: token,
                    email: email,
                    password: "",
                    password_confirmation: "",
                }}
                validationSchema={yup.object().shape({
                    email: yup
                        .string()
                        .required("An email address is required")
                        .email("Your email address must be valid"),
                    password: yup.string().required("A password is required"),
                    password_confirmation: yup
                        .string()
                        .required("You must confirm your password")
                        .oneOf(
                            [yup.ref("password"), null],
                            "Passwords must match"
                        ),
                })}
                onSubmit={onSubmit}
                submitTitle="Reset Password"
                // submitClass="w-full"
                hideClear
            >
                <TextInput
                    name="email"
                    type="email"
                    label="Email"
                    autoComplete="username"
                />
                <TextInput
                    name="password"
                    type="password"
                    label="Password"
                    autoComplete="new-password"
                />
                <TextInput
                    name="password_confirmation"
                    type="password"
                    label="Confirm Password"
                    autoComplete="new-password"
                />
            </Form>
        </CentralAuthServices>
    );
}
