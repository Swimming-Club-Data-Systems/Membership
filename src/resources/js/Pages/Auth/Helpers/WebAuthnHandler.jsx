import React, { useState, useEffect } from "react";
import { useField, useFormikContext } from "formik";
import useLogin from "./useLogin";
import Button from "@/Components/Button";

const WebAuthnHandler = () => {
    const supportsWebauthn = typeof PublicKeyCredential !== "undefined";

    const [hasWebauthn, setHasWebauthn] = useState(false);
    const [ssoUrl, setSsoUrl] = useState(null);
    const [error, setError] = useState(null);

    const [field, meta] = useField("email");

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
        }
    );

    const handleLogin = async (event, autoFill = false) => {
        const username = field.value;
        try {
            const requestObject = {};

            if (username) {
                requestObject.username = username;
                const hasTokens = await checkWebauthn();
                if (!hasTokens) {
                    setError({
                        variant: "warning",
                        message:
                            "There are no passkeys registered for this account.",
                    });
                    return;
                }
            }

            if (autoFill) {
                requestObject.credentialsGetProps = {
                    mediation: "conditional",
                };
            }

            const response = await login(requestObject);
            if (response.success) {
                window.location.replace(response.redirect_url);
                setError(null);
            } else {
                setError(webAuthnError);
                console.error(error);
            }
        } catch (error) {
            setError(webAuthnError);
            console.error(error);
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
            {
                // (supportsWebauthn && !showTraditional && hasWebauthn) &&
                <Button
                    variant="secondary"
                    onClick={handleLogin}
                    disabled={false}
                    className="mb-4 w-full"
                >
                    Login with passkey
                </Button>
            }
        </>
    );
};

export default WebAuthnHandler;
