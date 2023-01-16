import React, { useEffect, useState } from "react";
import Button from "@/Components/Button";
import CentralAuthServices from "@/Layouts/CentralAuthServices";
import { Head, router } from "@inertiajs/react";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import useLogin from "@/Pages/Auth/Helpers/useLogin";
import Alert from "@/Components/Alert";
import {
    browserSupportsWebAuthn,
    startAuthentication,
} from "@simplewebauthn/browser";
import axios from "@/Utils/axios";

export default function ConfirmPassword(props) {
    const [error, setError] = useState(null);
    const [canUsePlatformAuthenticator, setCanUsePlatformAuthenticator] =
        useState(false);

    useEffect(() => {
        (async () => {
            if (await browserSupportsWebAuthn()) {
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

    const handleWebAuthnLogin = async () => {
        let asseResp;

        const request = await axios.post(
            route("central.confirm-password.webauthn.challenge"),
            {}
        );

        try {
            // Pass the options to the authenticator and wait for a response
            asseResp = await startAuthentication(request.data);
        } catch (error) {
            // Some basic error handling
            setError({ ...webAuthnError, message: error.message });
        }

        // POST the response to the endpoint that calls
        // @simplewebauthn/server -> verifyAuthenticationResponse()
        const verificationResponse = await axios.post(
            route("central.confirm-password.webauthn.verify"),
            asseResp
        );

        if (verificationResponse.data.success) {
            router.visit(verificationResponse.data.redirect_url);
        } else {
            setError(webAuthnError);
            // console.error(error);
        }

        return;
    };

    return (
        <CentralAuthServices title="Confirm your password">
            <Head title="Confirm Password" />

            <div className="mb-4 text-sm text-gray-600">
                This is a secure area of the application. Please confirm your
                password before continuing.
            </div>

            {props.sso_url && (
                <>
                    <Button
                        href={route("central.confirm-password.oauth")}
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
                        method="post"
                        action={route("central.password.confirm")}
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
        </CentralAuthServices>
    );
}
