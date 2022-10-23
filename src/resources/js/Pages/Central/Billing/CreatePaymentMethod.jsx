import React, { useState } from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Layout from "@/Pages/Central/Tenants/Layout";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import { loadStripe } from "@stripe/stripe-js";
import {
    Elements,
    PaymentElement,
    useElements,
    useStripe,
} from "@stripe/react-stripe-js";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import Alert from "@/Components/Alert";

const SetupForm = (props) => {
    const stripe = useStripe();
    const elements = useElements();

    const [errorMessage, setErrorMessage] = useState(null);

    const onSubmit = async (values, formikBag) => {
        if (!stripe || !elements) {
            // Stripe.js has not yet loaded.
            // Make sure to disable form submission until Stripe.js has loaded.
            return;
        }

        const { error } = await stripe.confirmSetup({
            elements,
            confirmParams: {
                return_url: "https://example.com/order/123/complete",
            },
            // Uncomment below if you only want redirect for redirect-based payments
            redirect: "if_required",
        });

        if (error) {
            // Show error to your customer (for example, payment details incomplete)
            console.log(error.message);
            setErrorMessage(error.message);
        } else {
            // Your customer will be redirected to your `return_url`. For some payment
            // methods like iDEAL, your customer will be redirected to an intermediate
            // site first to authorize the payment, then redirected to the `return_url`.
        }

        formikBag.setSubmitting(false);
    };

    return (
        <Form onSubmit={onSubmit} hideDefaultButtons hideClear>
            <Card title="Payment Method Details" footer={<SubmissionButtons />}>
                <FlashAlert className="mb-4" />

                {errorMessage && (
                    <Alert
                        variant="error"
                        handleDismiss={() => setErrorMessage(null)}
                        title="Error"
                    >
                        {errorMessage}
                    </Alert>
                )}

                <PaymentElement />
            </Card>
        </Form>
    );
};

const CreatePaymentMethod = (props) => {
    const stripePromise = loadStripe(props.stripe_publishable);
    const options = {
        clientSecret: props.intent.client_secret,
        appearance: {
            theme: "none",
            labels: "above",
            variables: {
                fontFamily: "Inter var",
                colorPrimary: "#4f46e5",
                colorDanger: "#dc2626",
                spacingUnit: "0.75rem",
                borderRadius: "0.375rem",
                fontSizeBase: "0.875rem",
                fontSizeSm: "0.875rem",
            },
            rules: {
                ".Input": {
                    appearance: "none",
                    color: "#6b7280",
                    border: "1px solid #d1d5db",
                    padding: "0.5rem 0.75rem",
                    fontSize: "0.875rem",
                    lineHeight: "1.25rem",
                },
                ".Input:focus": {
                    borderColor: "#6366f1",
                },
            },
        },
    };

    return (
        <>
            <Elements stripe={stripePromise} options={options}>
                <Head title={`Add a payment method - ${props.name}`} />

                <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                    <SetupForm />
                </div>
            </Elements>
        </>
    );
};

CreatePaymentMethod.layout = (page) => (
    <CentralMainLayout
        title={`Add a payment method for ${page.props.name}`}
        subtitle={`Manage details for ${page.props.name}`}
    >
        <Layout children={page} />
    </CentralMainLayout>
);

export default CreatePaymentMethod;
