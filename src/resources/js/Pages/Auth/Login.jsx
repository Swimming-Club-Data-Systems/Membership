import React, { useEffect, useState } from "react";
import Checkbox from "@/Components/Form/Checkbox";
import AuthServices from "@/Layouts/AuthServices";
import { Inertia } from "@inertiajs/inertia";
import { Head } from "@inertiajs/inertia-react";
import Link from "@/Components/Link";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import Alert from "@/Components/Alert";
import WebAuthnHandler from "./Helpers/WebAuthnHandler";
import { Transition } from "@headlessui/react";
import SSOHandler from "@/Pages/Auth/Helpers/SSOHandler";

const Login = ({ status, canResetPassword }) => {
    const supportsWebauthn = typeof PublicKeyCredential !== "undefined";

    const [showPasswordField, setShowPasswordField] = useState(true);
    const [showWebauthn, setShowWebauthn] = useState(supportsWebauthn);
    const [ssoUrl, setSsoUrl] = useState(null);
    const [error, setError] = useState(null);
    const [autoComplete, setAC] = useState("");

    const onSubmit = (values, formikBag) => {
        Inertia.post(route("login"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    // If SSO, hide password and webauthn
    useEffect(() => {
        setShowPasswordField(ssoUrl === null);
        setShowWebauthn(ssoUrl === null);
    }, [ssoUrl]);

    const validationSchema = {
        email: yup
            .string()
            .required("An email address is required")
            .email("Your email address must be valid"),
    };

    if (!ssoUrl) {
        validationSchema.password = yup
            .string()
            .required("A password is required");
        validationSchema.remember = yup
            .boolean()
            .oneOf([false, true], "Remember me must be ticked or not ticked");
    }

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
                validationSchema={yup.object().shape(validationSchema)}
                onSubmit={onSubmit}
                submitTitle="Sign in"
                submitClass="w-full"
                hideClear
                hideDefaultButtons
            >
                <TextInput
                    name="email"
                    type="email"
                    label="Email"
                    autoComplete={autoComplete}
                />

                <Transition
                    show={showPasswordField}
                    enter="transition duration-500"
                    enterFrom="opacity-0 scale-0 height-0"
                    enterTo="opacity-100 scale-100 height-100"
                    leave="transition duration-150"
                    leaveFrom="opacity-100 scale-100 height-100"
                    leaveTo="opacity-0 scale-0 height-0"
                >
                    <TextInput
                        name="password"
                        type="password"
                        label="Password"
                        autoComplete="current-password"
                    />

                    <div className="flex items-center justify-between mb-4">
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
                </Transition>

                <div className="grid gap-y-4">
                    <SubmissionButtons />
                    <WebAuthnHandler setAC={setAC} show={showWebauthn} />
                </div>
                <SSOHandler setSsoUrl={setSsoUrl} />
            </Form>
        </AuthServices>
    );
};

export default Login;
