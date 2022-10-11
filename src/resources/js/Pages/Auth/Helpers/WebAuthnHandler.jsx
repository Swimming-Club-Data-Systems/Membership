import React, { useEffect, useState } from "react";
import { useField } from "formik";
import useLogin from "./useLogin";
import Button from "@/Components/Button";
import { Transition } from "@headlessui/react";
import { Inertia } from "@inertiajs/inertia";
import Alert from "@/Components/Alert";
import {
    browserSupportsWebAuthnAutofill,
    startAuthentication,
} from "@simplewebauthn/browser";
import axios from "@/Utils/axios";

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
        let asseResp;

        const request = await axios.post(route("webauthn.challenge"), {});

        try {
            // Pass the options to the authenticator and wait for a response
            asseResp = await startAuthentication(request.data, autoFill);
        } catch (error) {
            // Some basic error handling
            setError({ ...webAuthnError, message: error.message });
        }

        // POST the response to the endpoint that calls
        // @simplewebauthn/server -> verifyAuthenticationResponse()
        const verificationResponse = await axios.post(
            route("webauthn.verify"),
            asseResp
        );

        if (verificationResponse.data.success) {
            Inertia.visit(verificationResponse.data.redirect_url);
        } else {
            setError(webAuthnError);
            // console.error(error);
        }
    };

    useEffect(() => {
        (async () => {
            // await handleLogin(null, true);
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
                {error && (
                    <Alert className="mb-4" variant="error" title="Error">
                        {error.message}
                    </Alert>
                )}

                <Button
                    variant="secondary"
                    onClick={handleLogin}
                    disabled={false}
                    className="w-full mb-4"
                    type="button"
                >
                    Sign in with passkey
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
            </Transition>
        </>
    );
};

export default WebAuthnHandler;
