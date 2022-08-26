import React, { useEffect, useState } from "react";
import { useField } from "formik";
import useLogin from "./useLogin";
import Button from "@/Components/Button";
import { Transition } from "@headlessui/react";
import { Inertia } from "@inertiajs/inertia";

const WebAuthnHandler = ({ setAC, show }) => {
    const [hasWebauthn, setHasWebauthn] = useState(false);
    const [ssoUrl, setSsoUrl] = useState(null);
    const [error, setError] = useState(null);

    const [field] = useField("email");

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
            actionUrl: route("webauthn.verify"),
            actionHeader: {
                "X-XSRF-TOKEN": getCookie("XSRF-TOKEN"),
            },
            optionsUrl: route("webauthn.challenge"),
        },
        {
            "X-XSRF-TOKEN": getCookie("XSRF-TOKEN"),
        },
        setAC
    );

    const handleLogin = async (event, autoFill = false) => {
        const username = field.value;
        try {
            const requestObject = {};

            if (username) {
                requestObject.username = username;
                // const hasTokens = await checkWebauthn();
                // if (!hasTokens) {
                //     setError({
                //         variant: "warning",
                //         message:
                //             "There are no passkeys registered for this account.",
                //     });
                //     return;
                // }
            }

            if (autoFill) {
                requestObject.credentialsGetProps = {
                    mediation: "conditional",
                };
            }

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

    // eslint-disable-next-line no-unused-vars
    const handleAutofillLogin = async () => {
        // eslint-disable-next-line no-undef
        if (
            !PublicKeyCredential.isConditionalMediationAvailable ||
            // eslint-disable-next-line no-undef
            !PublicKeyCredential.isConditionalMediationAvailable()
        ) {
            // Browser doesn't support AutoFill-assisted requests.
            setAC("username");
            return;
        }

        await handleLogin(null, true);
    };

    useEffect(() => {
        (async () => {
            await handleAutofillLogin();
        })();
    }, []);

    return (
        <>
            <Transition
                show={show}
                enter="transition duration-500"
                enterFrom="opacity-0 scale-0 height-0"
                enterTo="opacity-100 scale-100 height-100"
                leave="transition duration-150"
                leaveFrom="opacity-100 scale-100 height-100"
                leaveTo="opacity-0 scale-0 height-0"
            >
                <Button
                    variant="secondary"
                    onClick={handleLogin}
                    disabled={false}
                    className="w-full"
                    type="button"
                >
                    Sign in with passkey
                </Button>
            </Transition>
        </>
    );
};

export default WebAuthnHandler;
