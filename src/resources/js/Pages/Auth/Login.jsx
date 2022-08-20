import React, { useState, useEffect } from "react";
import Checkbox from "@/Components/Form/Checkbox";
import AuthServices from "@/Layouts/AuthServices";
import { Inertia } from "@inertiajs/inertia";
import { Head } from "@inertiajs/inertia-react";
import Link from "@/Components/Link";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import Alert from "@/Components/Alert";
import useLogin from "./Helpers/useLogin";
import Button from "@/Components/Button";
import WebAuthnHandler from "./Helpers/WebAuthnHandler";

const Login = ({ status, canResetPassword }) => {
    const supportsWebauthn = typeof PublicKeyCredential !== "undefined";

    const [hasWebauthn, setHasWebauthn] = useState(false);
    const [ssoUrl, setSsoUrl] = useState(null);
    const [error, setError] = useState(null);

    const onSubmit = (values, formikBag) => {
        Inertia.post(route("login"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    return (
        <AuthServices title="Sign in to your account">
            <Head title="Log in" />

            {status && (
                <Alert title="Success" className="mb-4">
                    {status}
                </Alert>
            )}

            <Form
                initialValues={{
                    email: "",
                    password: "",
                    remember: false,
                }}
                validationSchema={yup.object().shape({
                    email: yup
                        .string()
                        .required("An email address is required")
                        .email("Your email address must be valid"),
                    password: yup.string().required("A password is required"),
                    remember: yup
                        .boolean()
                        .oneOf(
                            [false, true],
                            "Remember me must be ticked or not ticked"
                        ),
                })}
                onSubmit={onSubmit}
                submitTitle="Sign in"
                submitClass="w-full"
                hideClear
            >
                <TextInput
                    name="email"
                    type="email"
                    label="Email"
                    autoComplete="username webauthn"
                />
                <TextInput
                    name="password"
                    type="password"
                    label="Password"
                    autoComplete="current-password"
                />

                <div className="flex items-center justify-between">
                    <div className="flex items-center">
                        <Checkbox name="remember" label="Remember me" />
                    </div>

                    {canResetPassword && (
                        <div className="text-sm mb-3">
                            <Link href={route("password.request")}>
                                Forgot your password?
                            </Link>
                        </div>
                    )}
                </div>

                <WebAuthnHandler />
            </Form>
        </AuthServices>
    );
};

export default Login;
