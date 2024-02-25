import {
    ExpressCheckoutElement,
    useElements,
    useStripe,
} from "@stripe/react-stripe-js";
import React, { useState } from "react";
import { usePage } from "@inertiajs/react";
import { StripeExpressCheckoutElementOptions } from "@stripe/stripe-js";
import axios from "@/Utils/axios";
import Alert from "@/Components/Alert";

type ExpressCheckoutProps = {
    /** Path to the JSON route for creating an intent and returning the client secret */
    createIntentRoute: string;
    /** Object or function to POST to the `createIntentRoute` endpoint */
    createIntentPostData?:
        | (() => { [key: string]: any })
        | { [key: string]: any };
};

export const ExpressCheckout = ({
    createIntentRoute,
    createIntentPostData = {},
}: ExpressCheckoutProps) => {
    const stripe = useStripe();
    const elements = useElements();
    const [errorMessage, setErrorMessage] = useState<string>(null);
    const [hasExpressCheckoutOptions, setHasExpressCheckoutOptions] =
        useState(false);
    const { props: pageProps } = usePage();

    const expressCheckoutOptions: StripeExpressCheckoutElementOptions = {
        wallets: {
            applePay: "always",
            googlePay: "always",
        },
        buttonType: {
            applePay: "check-out",
            googlePay: "checkout",
            // paypal: "checkout",
        },
    };

    const onExpressCheckoutReady = ({ availablePaymentMethods }) => {
        if (!availablePaymentMethods) {
            // No buttons will show
        } else {
            // Optional: Animate in the Element
            setHasExpressCheckoutOptions(true);
        }
    };

    const onExpressCheckoutClick = ({ resolve }) => {
        const options = {
            business: {
                // @ts-ignore
                name: pageProps.tenant.name,
            },
            emailRequired: true,
        };
        resolve(options);
    };

    const onExpressCheckoutConfirm = async () => {
        if (!stripe) {
            // Stripe.js hasn't loaded yet.
            // Make sure to disable form submission until Stripe.js has loaded.
            return;
        }

        const { error: submitError } = await elements.submit();
        if (submitError) {
            setErrorMessage(submitError.message);
            return;
        }

        // Call the server
        // Create the PaymentIntent and obtain clientSecret

        try {
            const data =
                typeof createIntentPostData === "function"
                    ? createIntentPostData()
                    : createIntentPostData;

            const res = await axios.post(createIntentRoute, data);
            const { client_secret: clientSecret, return_url: returnUrl } =
                res.data;

            // Confirm the PaymentIntent using the details collected by the Express Checkout Element
            const { error } = await stripe.confirmPayment({
                // `elements` instance used to create the Express Checkout Element
                elements,
                // `clientSecret` from the created PaymentIntent
                clientSecret,
                confirmParams: {
                    return_url: returnUrl,
                },
            });

            if (error) {
                // This point is only reached if there's an immediate error when
                // confirming the payment. Show the error to your customer (for example, payment details incomplete)
                setErrorMessage(error.message);
            } else {
                // The payment UI automatically closes with a success animation.
                // Your customer is redirected to your `return_url`.
            }
        } catch (error) {
            console.error(error);
            if (error.response) {
                setErrorMessage(error.response.data.message);
            } else if (error.request) {
                setErrorMessage(
                    "An error occurred during a request to the SCDS server. You will not be charged. Please try again later.",
                );
            } else {
                setErrorMessage(
                    "An unknown error occurred. You will not be charged. Please try again later.",
                );
            }
        }
    };

    return (
        <>
            {errorMessage && (
                <Alert title="Error" variant="error">
                    {errorMessage}
                </Alert>
            )}

            <ExpressCheckoutElement
                onReady={onExpressCheckoutReady}
                onClick={onExpressCheckoutClick}
                onConfirm={onExpressCheckoutConfirm}
                options={expressCheckoutOptions}
            />

            {hasExpressCheckoutOptions && (
                <div className="relative mb-4">
                    <div className="absolute inset-0 flex items-center">
                        <div className="w-full border-t border-gray-300" />
                    </div>
                    <div className="relative flex justify-center text-sm">
                        <span className="bg-white px-2 text-gray-500">Or</span>
                    </div>
                </div>
            )}
        </>
    );
};
