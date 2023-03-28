import React, { useEffect, useState } from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import MainHeader from "@/Layouts/Components/MainHeader";
import {
    Elements,
    PaymentElement,
    useElements,
    useStripe,
} from "@stripe/react-stripe-js";
import { loadStripe } from "@stripe/stripe-js";
import Form from "@/Components/Form/Form";
import * as yup from "yup";

type Props = {
    id: number;
    stripe_publishable_key: string;
    client_secret: string;
    stripe_account: string;
};

const CheckoutForm: React.FC = () => {
    const stripe = useStripe();
    const elements = useElements();

    const handleStripeSubmit = async (ev) => {
        if (!stripe || !elements) {
            // Stripe.js has not yet loaded.
            // Make sure to disable form submission until Stripe.js has loaded.
            return;
        }

        const result = await stripe.confirmPayment({
            //`Elements` instance that was used to create the Payment Element
            elements,
            confirmParams: {
                return_url: "https://example.com/order/123/complete",
            },
            redirect: "if_required",
        });

        if (result.error) {
            // Show error to your customer (for example, payment details incomplete)
            console.log(result.error.message);
        } else if (result.paymentIntent) {
            // We need to handle redirects if the payment was successful
            // Doing it this way allows us to use the Inertia SPA routing rather than a reload
        } else {
            // Your customer will be redirected to your `return_url`. For some payment
            // methods like iDEAL, your customer will be redirected to an intermediate
            // site first to authorize the payment, then redirected to the `return_url`.
        }
    };

    return (
        <Form
            initialValues={{}}
            validationSchema={yup.object({})}
            onSubmit={handleStripeSubmit}
            submitTitle="Pay"
            hideClear
        >
            <div className="grid gap-4">
                <PaymentElement options={{ layout: "accordion" }} />
            </div>
        </Form>
    );
};

const Checkout: Layout<Props> = (props: Props) => {
    const [stripe, setStripe] = useState(null);

    const appearance = {
        theme: "stripe",
        variables: {
            fontSizeBase: "0.875rem",
            colorPrimary: "#4f46e5",
            colorBackground: "#ffffff",
            colorText: "#111827",
            colorDanger: "#7f1d1d",
            fontFamily:
                '"Inter var", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
            spacingUnit: "4px",
            spacingGridColumn: "1rem",
            spacingGridRow: "1rem",
            borderRadius: "4px",
            // See all possible variables below
        },
    };
    const options = {
        clientSecret: props.client_secret,
        appearance,
    };

    useEffect(() => {
        loadStripe(props.stripe_publishable_key, {
            stripeAccount: props.stripe_account,
        }).then((value) => {
            if (value) {
                setStripe(value);
            }
        });
    }, []);

    return (
        <>
            <Head
                title={`Checkout`}
                breadcrumbs={[
                    { name: "Payments", route: "my_account.index" },
                    // { name: "Checkout", route: "payments.statements.index" },
                    // {
                    //     name: `#${props.id}`,
                    //     route: "payments.statements.show",
                    //     routeParams: props.id,
                    // },
                ]}
            />

            <MainHeader
                title={`SCDS Checkout`}
                subtitle="Pay for stuff"
            ></MainHeader>

            {stripe && (
                <Elements stripe={stripe} options={options}>
                    <CheckoutForm />
                </Elements>
            )}
        </>
    );
};

Checkout.layout = (page) => (
    <MainLayout hideHeader>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Checkout;
