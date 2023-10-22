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
import {
    PaymentIntentResult,
    loadStripe,
    StripeElementsOptions,
} from "@stripe/stripe-js";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import * as yup from "yup";
import Alert from "@/Components/Alert";
import Select from "@/Components/Form/Select";
import { router, usePage } from "@inertiajs/react";
import { PaymentLineItemsSummary } from "@/Components/Payments/Checkout/PaymentLineItemsSummary";
import ButtonLink from "@/Components/ButtonLink";
import { appearance } from "@/Utils/Stripe/Appearance";

type Props = {
    auth: {
        user?: {
            name: string;
        };
    };
    id: number;
    stripe_publishable_key: string;
    client_secret: string;
    stripe_account: string;
    country: string;
    currency: string;
    total: number;
    payment_methods: {
        stripe_id: string;
        description: string;
    }[];
    return_url: string;
    formatted_total: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    customer_address: {
        line1: string;
        line2: string;
        city: string;
        country: string;
        postal_code: string;
    };
    lines: Record<string, never>[];
    return_link?: string;
    return_link_text?: string;
    cancel_link?: string;
    cancel_link_text?: string;
};

const CheckoutForm: React.FC<Props> = (props: Props) => {
    const stripe = useStripe();
    const elements = useElements();

    const [submitting, setSubmitting] = useState(false);
    const [paymentRequest, setPaymentRequest] = useState(null);

    const paymentMethods = [
        {
            stripe_id: "n/a",
            description: "Choose a saved payment method",
        },
        ...props.payment_methods,
    ];

    useEffect(() => {
        if (stripe) {
            const pr = stripe.paymentRequest({
                country: props.country,
                currency: props.currency,
                total: {
                    label: "Total",
                    amount: props.total,
                },
                requestPayerName: true,
                requestPayerEmail: true,
            });

            // Check the availability of the Payment Request API.
            pr.canMakePayment().then((result) => {
                if (result) {
                    setPaymentRequest(pr);
                }
            });
        }
    }, [props.country, props.currency, props.total, stripe]);

    const [alert, setAlert] = useState(null);

    const handleStripeSubmit = async (values, formik) => {
        if (!stripe || !elements) {
            // Stripe.js has not yet loaded.
            // Make sure to disable form submission until Stripe.js has loaded.
            return;
        }

        let alertName = "";

        setSubmitting(true);

        let result: PaymentIntentResult;
        if (values.payment_method) {
            alertName = "saved_cards";
            // Try to charge the specified payment method
            result = await stripe.confirmCardPayment(props.client_secret, {
                payment_method: values.payment_method,
            });
        } else {
            alertName = "payment_element";
            result = await stripe.confirmPayment({
                //`Elements` instance that was used to create the Payment Element
                elements,
                confirmParams: {
                    return_url: props.return_url,
                },
                redirect: "if_required",
            });
        }

        if (result.error) {
            // Show error to your customer (for example, payment details incomplete)
            let message = "Please try another payment method.";

            if (
                result.error.type === "card_error" ||
                result.error.type === "validation_error"
            ) {
                message = result.error.message;
            }

            setAlert({
                variant: "error",
                title: "Payment failed",
                message: message,
                name: alertName,
            });

            setSubmitting(false);
            formik.setSubmitting(false);
        } else if (result.paymentIntent) {
            // We need to handle redirects if the payment was successful
            // Doing it this way allows us to use the Inertia SPA routing rather than a reload
            switch (result.paymentIntent.status) {
                case "succeeded":
                    // setMessage('Success! Payment received.');
                    router.visit(route("payments.checkout.success", props.id), {
                        replace: true,
                    });
                    break;

                case "processing":
                    router.visit(route("payments.checkout.success", props.id), {
                        replace: true,
                    });
                    break;

                case "requires_payment_method":
                    setAlert({
                        variant: "error",
                        title: "Payment failed",
                        message: "Please try another payment method.",
                        name: alertName,
                    });
                    break;

                default:
                    setAlert({
                        variant: "error",
                        title: "Something went wrong",
                        message: "Please try again.",
                        name: alertName,
                    });
                    // setMessage('Something went wrong.');
                    break;
            }
        } else {
            setSubmitting(false);
            formik.setSubmitting(false);
            // Your customer will be redirected to your `return_url`. For some payment
            // methods like iDEAL, your customer will be redirected to an intermediate
            // site first to authorize the payment, then redirected to the `return_url`.
        }
    };

    return (
        <>
            <Container>
                <div className="grid grid-cols-12">
                    <div className="grid col-span-full md:col-span-6 gap-4">
                        {paymentRequest && (
                            <>
                                {/*<PaymentRequestButtonElement*/}
                                {/*    options={{ paymentRequest }}*/}
                                {/*/>*/}

                                {/*<div className="relative mb-4">*/}
                                {/*    <div className="absolute inset-0 flex items-center">*/}
                                {/*        <div className="w-full border-t border-gray-300" />*/}
                                {/*    </div>*/}
                                {/*    <div className="relative flex justify-center text-sm">*/}
                                {/*        <span className="bg-gray-100 px-2 text-gray-500">*/}
                                {/*            Or*/}
                                {/*        </span>*/}
                                {/*    </div>*/}
                                {/*</div>*/}
                            </>
                        )}

                        {props.payment_methods.length > 0 && (
                            <>
                                <Form
                                    initialValues={{
                                        payment_method: "n/a",
                                    }}
                                    validationSchema={yup.object({
                                        payment_method: yup
                                            .string()
                                            .required()
                                            .test({
                                                name: "test_valid_pm",
                                                test: (value) =>
                                                    value !== "n/a",
                                                message:
                                                    "Please select a payment method.",
                                            }),
                                    })}
                                    onSubmit={handleStripeSubmit}
                                    submitTitle={`Pay ${props.formatted_total} now`}
                                    hideClear
                                    hideDefaultButtons
                                    disabled={submitting}
                                >
                                    <div className="grid gap-4">
                                        <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight">
                                            Use a saved payment method
                                        </h2>
                                        {alert && alert.name === "saved_cards" && (
                                            <Alert
                                                title={alert.title}
                                                variant={alert.variant}
                                            >
                                                <p>{alert.message}</p>
                                            </Alert>
                                        )}

                                        <Select
                                            name="payment_method"
                                            items={paymentMethods.map(
                                                (payment_method) => {
                                                    return {
                                                        disabled:
                                                            payment_method.stripe_id ===
                                                            "n/a",
                                                        value: payment_method.stripe_id,
                                                        name: payment_method.description,
                                                    };
                                                }
                                            )}
                                            label="Choose a saved payment method"
                                        />

                                        <SubmissionButtons />
                                    </div>
                                </Form>

                                <div className="relative mb-4">
                                    <div className="absolute inset-0 flex items-center">
                                        <div className="w-full border-t border-gray-300" />
                                    </div>
                                    <div className="relative flex justify-center text-sm">
                                        <span className="bg-gray-100 px-2 text-gray-500">
                                            Or
                                        </span>
                                    </div>
                                </div>
                            </>
                        )}

                        <Form
                            initialValues={{}}
                            validationSchema={yup.object({})}
                            onSubmit={handleStripeSubmit}
                            submitTitle={`Pay ${props.formatted_total} now`}
                            hideClear
                            hideDefaultButtons
                            disabled={submitting}
                        >
                            <div className="grid gap-4">
                                <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight">
                                    Use a new or other payment method
                                </h2>
                                {alert && alert.name === "payment_element" && (
                                    <Alert
                                        title={alert.title}
                                        variant={alert.variant}
                                    >
                                        <p>{alert.message}</p>
                                    </Alert>
                                )}

                                <PaymentElement
                                    options={{
                                        layout: "accordion",
                                        defaultValues: {
                                            billingDetails: {
                                                name: props.customer_name,
                                                email: props.customer_email,
                                                phone: props.customer_phone,
                                                address: {
                                                    line1: props
                                                        .customer_address.line1,
                                                    line2: props
                                                        .customer_address.line2,
                                                    city: props.customer_address
                                                        .city,
                                                    country:
                                                        props.customer_address
                                                            .country,
                                                    postal_code:
                                                        props.customer_address
                                                            .postal_code,
                                                },
                                            },
                                        },
                                        business: {
                                            name: usePage().props?.tenant?.name,
                                        },
                                        paymentMethodOrder: [
                                            "apple_pay",
                                            "google_pay",
                                            "card",
                                            "bacs_debit",
                                        ],
                                        // wallets: {
                                        //     // We use a separate Payment Request button at the top of the page
                                        //     // applePay: "never",
                                        //     // googlePay: "never",
                                        // },
                                    }}
                                />

                                <SubmissionButtons />
                            </div>
                        </Form>
                    </div>
                    <div className="col-span-full md:col-span-5 md:col-start-8">
                        <div className="grid gap-4">
                            <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight">
                                Payment Summary
                            </h2>
                            <div className="-mx-4 sm:mx-0">
                                <PaymentLineItemsSummary data={props.lines} />
                            </div>
                            {props.cancel_link && (
                                <>
                                    <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight">
                                        Unsure about continuing?
                                    </h2>
                                    <p>
                                        <ButtonLink
                                            href={props.cancel_link}
                                            variant="danger"
                                        >
                                            {props.cancel_link_text ||
                                                "Cancel and return"}
                                        </ButtonLink>
                                    </p>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </Container>
        </>
    );
};

const Checkout: Layout<Props> = (props: Props) => {
    const [stripe, setStripe] = useState(null);

    const options: StripeElementsOptions = {
        clientSecret: props.client_secret,
        appearance: appearance,
        fonts: [
            {
                cssSrc: "https://rsms.me/inter/inter.css",
            },
        ],
    };

    useEffect(() => {
        loadStripe(props.stripe_publishable_key, {
            stripeAccount: props.stripe_account,
        }).then((value) => {
            if (value) {
                setStripe(value);
            }
        });
    }, [props.stripe_account, props.stripe_publishable_key]);

    const routes = props.auth?.user
        ? [{ name: "Payments", route: "payments.index" }]
        : [];

    return (
        <>
            <Head title={`Checkout`} breadcrumbs={routes} />

            <Container>
                <MainHeader
                    title={`Pay ${props.formatted_total}`}
                    subtitle="with SCDS Checkout"
                ></MainHeader>
            </Container>

            {stripe && (
                <Elements stripe={stripe} options={options}>
                    <CheckoutForm {...props} />
                </Elements>
            )}
        </>
    );
};

Checkout.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Checkout;
