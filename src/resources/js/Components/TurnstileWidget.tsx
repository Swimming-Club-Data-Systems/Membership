import { usePage } from "@inertiajs/react";
import { Turnstile } from "@marsidev/react-turnstile";
import React from "react";
import Container from "@/Components/Container";
import axios from "@/Utils/axios";

export const TurnstileWidget = () => {
    // @ts-ignore
    const siteKey = usePage().props.cf?.site_key;

    if (!siteKey) return null;

    const validateToken = async (token: string): Promise<boolean> => {
        const response = await axios.post(route("validate_turnstile_widget"), {
            token: token,
        });

        return response.data.status === "success";
    };

    return (
        <Container>
            <Turnstile
                siteKey={siteKey}
                className="mx-auto"
                options={{
                    theme: "light",
                }}
                onSuccess={async (token: string) => {
                    const valid = await validateToken(token);
                }}
            />
        </Container>
    );
};
