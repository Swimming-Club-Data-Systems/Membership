import React, { useEffect, useState } from "react";
import Button from "@/Components/Button";
import AuthServices from "@/Layouts/AuthServices";
import { Head } from "@inertiajs/inertia-react";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import { Inertia } from "@inertiajs/inertia";
import useLogin from "@/Pages/Auth/Helpers/useLogin";
import Alert from "@/Components/Alert";
import { platformAuthenticatorIsAvailable } from "@simplewebauthn/browser";

export default function ConfirmPassword(props) {
    const [error, setError] = useState(null);
    const [canUsePlatformAuthenticator, setCanUsePlatformAuthenticator] =
        useState(false);

    useEffect(() => {
        (async () => {
            if (await platformAuthenticatorIsAvailable()) {
                setCanUsePlatformAuthenticator(true);
            }
        })();
    }, []);

    const getCookie = (cName) => {
        const name = cName + "=";
        const cDecoded = decodeURIComponent(document.cookie); //to be careful
        const cArr = cDecoded.split("; ");
        let res;
        cArr.forEach((val) => {
            if (val.indexOf(name) === 0) res = val.substring(name.length);
        });
        return res;
    };

    const webAuthnError = {
        type: "danger",
        message: "Passkey authentication failed.",
    };

    const login = useLogin(
        {
            actionUrl: route("confirm-password.webauthn.verify"),
            actionHeader: {
                "X-XSRF-TOKEN": getCookie("XSRF-TOKEN"),
            },
            optionsUrl: route("confirm-password.webauthn.challenge"),
        },
        {
            "X-XSRF-TOKEN": getCookie("XSRF-TOKEN"),
        }
    );

    const handleWebAuthnLogin = async () => {
        try {
            const requestObject = {};

            const response = await login(requestObject);
            if (response.success) {
                Inertia.visit(response.redirect_url);
                // window.location.replace(response.redirect_url);
                setError(null);
            } else {
                setError(webAuthnError);
                // console.error(error);
            }
        } catch (error) {
            setError(webAuthnError);
            // console.error(error);
        }
    };

    const onSubmit = (values, formikBag) => {
        Inertia.post(route("password.confirm"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    return (
        <AuthServices title="Confirm your password">
            <Head title="Confirm Password" />

            <div className="mb-4 text-sm text-gray-600">
                This is a secure area of the application. Please confirm your
                password before continuing.
            </div>

            {props.sso_url && (
                <>
                    <Button
                        href={route("confirm-password.oauth")}
                        variant="primary"
                        className="w-full"
                    >
                        Confirm with SSO
                    </Button>
                </>
            )}

            {!props.sso_url && (
                <>
                    {props.has_webauthn && canUsePlatformAuthenticator && (
                        <>
                            {error && (
                                <Alert
                                    variant="error"
                                    title="Error"
                                    className="mb-4"
                                >
                                    {error.message}
                                </Alert>
                            )}
                            <Button
                                variant="secondary"
                                onClick={handleWebAuthnLogin}
                                disabled={false}
                                className="w-full mb-4"
                                type="button"
                            >
                                Confirm with passkey
                            </Button>

                            <div className="relative mb-4">
                                <div className="absolute inset-0 flex items-center">
                                    <div className="w-full border-t border-gray-300" />
                                </div>
                                <div className="relative flex justify-center text-sm">
                                    <span className="bg-white px-2 text-gray-500">
                                        Or use your password
                                    </span>
                                </div>
                            </div>
                        </>
                    )}

                    <Form
                        initialValues={{
                            password: "",
                        }}
                        validationSchema={yup.object().shape({
                            password: yup
                                .string()
                                .required("A password is required"),
                        })}
                        action={route("password.confirm")}
                        method="post"
                        submitTitle="Confirm"
                        hideClear
                    >
                        <TextInput
                            name="password"
                            type="password"
                            autoFocus
                            autoComplete="password"
                        />
                    </Form>
                </>
            )}

            {/* <ValidationErrors errors={errors} />*/}
        </AuthServices>
    );
}
