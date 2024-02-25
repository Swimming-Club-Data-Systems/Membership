import { usePage } from "@inertiajs/react";
import { Turnstile } from "@marsidev/react-turnstile";
import React, { useEffect } from "react";
import Container from "@/Components/Container";
import axios from "@/Utils/axios";
import MainHeader from "@/Layouts/Components/MainHeader";
import { useDispatch, useSelector } from "react-redux";
import { hideTurnstile, RootState } from "@/Reducers/store";
import Modal from "@/Components/Modal";

export const TurnstileWidget = ({ children }) => {
    // @ts-ignore
    const siteKey = usePage().props.cf?.site_key;

    const showTurnstile = useSelector(
        (state: RootState) => state.showCloudflareTurnstile.value,
    );
    const dispatch = useDispatch();

    const validateToken = async (token: string): Promise<boolean> => {
        const response = await axios.post(route("validate_turnstile_widget"), {
            token: token,
        });

        return response.data.success;
    };

    return (
        <>
            {children}

            {siteKey && (
                <>
                    <Modal
                        show={showTurnstile}
                        onClose={() => {
                            dispatch(hideTurnstile());
                        }}
                        title="One moment please"
                    >
                        <p className="text-sm mb-3 text-gray-800">
                            We're checking if the site connection is secure.
                        </p>

                        <p className="text-sm mb-3 text-gray-800">
                            Once you complete the captcha, please retry your
                            request.
                        </p>

                        <Turnstile
                            siteKey={siteKey}
                            className=""
                            options={{
                                theme: "light",
                            }}
                            onSuccess={async (token: string) => {
                                const valid = await validateToken(token);

                                if (valid) {
                                    dispatch(hideTurnstile());
                                }
                            }}
                        />
                    </Modal>
                </>
            )}
        </>
    );
};
