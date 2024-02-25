import { usePage } from "@inertiajs/react";
import { Turnstile } from "@marsidev/react-turnstile";
import React, { useEffect } from "react";
import Container from "@/Components/Container";
import axios from "@/Utils/axios";
import MainHeader from "@/Layouts/Components/MainHeader";

export const TurnstileWidget = ({ children }) => {
    // @ts-ignore
    const siteKey = usePage().props.cf?.site_key;

    const [shouldDisplay, setShouldDisplay] = React.useState<boolean>(true);

    const validateToken = async (token: string): Promise<boolean> => {
        const response = await axios.post(route("validate_turnstile_widget"), {
            token: token,
        });

        return response.data.status === "success";
    };

    if (!siteKey || !shouldDisplay) return <>{children}</>;

    return (
        <Container>
            <main className="my-4 min-h-screen">
                <MainHeader
                    title="One moment please"
                    subtitle="We're checking if the site connection is secure"
                ></MainHeader>

                <Turnstile
                    siteKey={siteKey}
                    className=""
                    options={{
                        theme: "light",
                    }}
                    onSuccess={async (token: string) => {
                        const valid = await validateToken(token);

                        if (valid) {
                            setShouldDisplay(false);
                        }
                    }}
                />
            </main>
        </Container>
    );
};
